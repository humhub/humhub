<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\marketplace;

use humhub\components\listing\ArrayListBuilder;
use humhub\components\listing\filters\BoolFilter;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListEvent;
use humhub\components\listing\ListValidationException;
use humhub\components\ModuleManager;
use humhub\modules\marketplace\components\ModuleList;
use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\modules\marketplace\models\Module;
use humhub\modules\marketplace\services\MarketplaceListService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

/**
 * The marketplace's module list over the catalogue of {@see MarketplaceListServiceTest}.
 *
 * @since 1.20
 */
class ModuleListTest extends HumHubDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->cache->set(OnlineModuleManager::CACHE_KEY_MODULES, MarketplaceListServiceTest::catalogue());
        Yii::$app->getModule('marketplace')->settings->delete('includeCommunityModules');
    }

    protected function tearDown(): void
    {
        Event::off(ModuleList::class, ModuleList::EVENT_INIT);
        Yii::$app->cache->delete(OnlineModuleManager::CACHE_KEY_MODULES);
        Yii::$app->getModule('marketplace')->settings->delete('includeCommunityModules');
        parent::tearDown();
    }

    private function ids(array $params = []): array
    {
        $list = new ModuleList(new MarketplaceListService(new OnlineModuleManager()));

        return array_values(array_map(static fn(Module $module) => $module->id, $list->build($params, new ListContext())->items()));
    }

    private function errors(array $params): array
    {
        try {
            (new ModuleList(new MarketplaceListService(new OnlineModuleManager())))->build($params, new ListContext());
        } catch (ListValidationException $e) {
            return $e->errors;
        }
        $this->fail('the parameters are refused');
    }

    public function testSortsUpdatesFirstThenNotInstalledThenInstalled(): void
    {
        $this->assertSame(['friendship', 'calendar-x', 'polls-x', 'pro-x', 'paid-x', 'bought-x', 'like'], $this->ids());
    }

    public function testFiltersByStatus(): void
    {
        $this->assertSame(['friendship'], $this->ids(['status' => ['update']]));
        $this->assertSame(['like'], $this->ids(['status' => 'installed']));
        $this->assertSame(['friendship', 'like'], $this->ids(['status' => 'update,installed']));
    }

    public function testFiltersByKeyword(): void
    {
        $this->assertSame(['calendar-x'], $this->ids(['q' => 'events']));
    }

    public function testFiltersByCategory(): void
    {
        $this->assertSame(['friendship', 'calendar-x'], $this->ids(['categoryId' => 1]));
        $this->assertSame(['pro-x', 'paid-x', 'bought-x', 'like'], $this->ids(['categoryId' => '-1']));
        $this->assertCount(7, $this->ids(['categoryId' => '0']), '0 = all');
    }

    public function testFiltersByAnyOfTheTags(): void
    {
        $this->assertSame(['calendar-x'], $this->ids(['tag' => ['featured']]));
        $this->assertSame(['polls-x', 'bought-x'], $this->ids(['tag' => 'partner,purchased']));
    }

    public function testFiltersByTheCommunityTag(): void
    {
        Yii::$app->getModule('marketplace')->settings->set('includeCommunityModules', true);

        $this->assertSame(['community-x'], $this->ids(['tag' => ['community']]));
    }

    public function testFiltersByAnyOfTheUseCases(): void
    {
        $this->assertSame(['calendar-x', 'polls-x'], $this->ids(['useCase' => ['intranet']]));
        $this->assertSame(['calendar-x', 'pro-x'], $this->ids(['useCase' => 'education']));
        $this->assertSame(['bought-x'], $this->ids(['useCase' => ['higher-education']]));
        $this->assertSame(['calendar-x', 'polls-x', 'pro-x'], $this->ids(['useCase' => 'intranet,education']));
    }

    public function testIdSelectsOneModuleRegardlessOfTheOtherFilters(): void
    {
        $this->assertSame(['like'], $this->ids(['id' => 'like', 'status' => ['notInstalled']]));
        $this->assertSame([], $this->ids(['id' => 'unknown']));
    }

    public function testRejectsInvalidValuesAndUnknownParameters(): void
    {
        $errors = $this->errors([
            'status' => 'broken',
            'tag' => 'professional,cheap',
            'useCase' => 'Bad Value!',
            'categoryId' => 'not-a-number',
            'sort' => 'name',
            'keyword' => 'old',
        ]);

        $this->assertEqualsCanonicalizing(['status', 'tag', 'useCase', 'categoryId', 'sort', 'keyword'], array_keys($errors));
        $this->assertSame(['Unknown value "cheap".'], $errors['tag']);
    }

    public function testTheModuleManagersFilterEventHasItsSay(): void
    {
        $fired = 0;
        $handler = static function ($event) use (&$fired) {
            $fired++;
            unset($event->modules['calendar-x']);
        };
        Yii::$app->moduleManager->on(ModuleManager::EVENT_AFTER_FILTER_MODULES, $handler);

        try {
            $this->assertSame(['friendship', 'polls-x', 'pro-x', 'paid-x', 'bought-x', 'like'], $this->ids());
            $this->assertSame(1, $fired, 'once per list');
            $this->assertSame([], $this->ids(['q' => 'events']), 'also for a keyword search');
        } finally {
            Yii::$app->moduleManager->off(ModuleManager::EVENT_AFTER_FILTER_MODULES, $handler);
        }
    }

    public function testModulesAddAFilter(): void
    {
        Event::on(ModuleList::class, ModuleList::EVENT_INIT, static function (ListEvent $event) {
            $event->list->addFilter(new BoolFilter(
                'paid',
                apply: static fn(ArrayListBuilder $list, bool $paid) => $list->filter(static fn(Module $module) => !empty($module->price_eur) === $paid),
                definition: ['label' => 'Paid', 'sortOrder' => 600],
            ));
        });

        $this->assertSame(['paid-x', 'bought-x'], $this->ids(['paid' => '1']));
        $this->assertSame('paid', array_column((new ModuleList())->definitions(new ListContext()), 'key')[5]);
    }

    public function testTheMarketplaceDefinitions(): void
    {
        $definitions = (new ModuleList())->definitions(new ListContext());

        $this->assertSame(['q', 'status', 'tag', 'useCase', 'categoryId', 'id'], array_column($definitions, 'key'));
        $this->assertSame(['text', 'select', 'select', 'select', 'select', 'text'], array_column($definitions, 'type'));
        $this->assertSame('Search Modules...', $definitions[0]['placeholder']);
        $this->assertSame(['installed', 'notInstalled', 'update'], array_column($definitions[1]['options'], 'value'));
        $this->assertSame(ModuleList::TAGS, array_column($definitions[2]['options'], 'value'));
        $this->assertStringContainsString('api/v2/marketplace/use-case', $definitions[3]['optionsUrl']);
        $this->assertArrayNotHasKey('options', $definitions[3]);
        $this->assertStringContainsString('api/v2/marketplace/category', $definitions[4]['optionsUrl']);
        $this->assertSame(['key' => 'id', 'type' => 'text', 'hidden' => true, 'placement' => 'primary'], $definitions[5]);
    }
}

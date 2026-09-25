<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\filter;

use humhub\components\filter\FilterSet;
use humhub\modules\marketplace\components\MarketplaceFilterSet;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;
use yii\base\InvalidConfigException;

class FilterSetTest extends HumHubDbTestCase
{
    protected function tearDown(): void
    {
        Event::off(TestFilterSet::class, FilterSet::EVENT_INIT);
        parent::tearDown();
    }

    public function testExportsDefinitionsSortedWithTheirKey(): void
    {
        $this->assertSame([
            ['type' => 'text', 'label' => 'Search', 'key' => 'q'],
            ['type' => 'tags', 'multiple' => true, 'options' => [['value' => 'a', 'label' => 'A']], 'key' => 'tag'],
        ], (new TestFilterSet())->toArray());
    }

    public function testModulesAddAndRemoveFiltersOnInit(): void
    {
        Event::on(TestFilterSet::class, FilterSet::EVENT_INIT, static function (Event $event) {
            $event->sender->removeFilter('tag');
            $event->sender->addFilter('extra', ['type' => 'checkbox', 'label' => 'Extra', 'sortOrder' => 50]);
        });

        $this->assertSame(['extra', 'q'], array_column((new TestFilterSet())->toArray(), 'key'));
    }

    public function testRejectsAnUnknownType(): void
    {
        $this->expectException(InvalidConfigException::class);
        (new TestFilterSet())->addFilter('x', ['type' => 'dropdown']);
    }

    public function testTheMarketplaceFilters(): void
    {
        $filters = (new MarketplaceFilterSet())->toArray();

        $this->assertSame(['q', 'status', 'tag', 'useCase', 'categoryId', 'id'], array_column($filters, 'key'));
        $this->assertSame('text', $filters[0]['type']);
        $this->assertSame('select', $filters[1]['type']);
        $this->assertSame('select', $filters[2]['type']);
        $this->assertSame('select', $filters[3]['type']);
        $this->assertSame('select', $filters[4]['type']);
        $this->assertSame('text', $filters[5]['type']);
        $this->assertTrue($filters[5]['hidden']);
        $this->assertStringContainsString('api/v2/marketplace/use-case', $filters[3]['optionsUrl']);
        $this->assertStringContainsString('api/v2/marketplace/category', $filters[4]['optionsUrl']);
    }
}

class TestFilterSet extends FilterSet
{
    protected function initDefaultFilters(): void
    {
        $this->addFilter('tag', ['type' => 'tags', 'multiple' => true, 'options' => [['value' => 'a', 'label' => 'A']], 'sortOrder' => 200]);
        $this->addFilter('q', ['type' => 'text', 'label' => 'Search', 'sortOrder' => 100]);
    }
}

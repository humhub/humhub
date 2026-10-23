<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\commands;

use humhub\models\ModuleEnabled;
use humhub\modules\marketplace\commands\MarketplaceController;
use humhub\modules\marketplace\components\OnlineModuleManager;
use ReflectionMethod;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\ServerErrorHttpException;

/**
 * `module/update-all` downloads all packages first, checks their module dependencies against
 * the versions installed once all updates are applied, and updates dependencies first.
 *
 * Uses the fixture modules in `_data/ModuleDependencies` (`dep-parent` 2.0.0, `dep-child` 1.0.0,
 * `dep-other` 1.0.0), the downloaded packages are stubbed.
 *
 * @since 1.19.1
 */
class MarketplaceControllerTest extends HumHubDbTestCase
{
    private const FIXTURE_MODULE_IDS = ['dep-parent', 'dep-other', 'dep-child'];

    private array $originalAutoloadPaths;
    private ?OnlineModuleManager $originalOnlineModuleManager;
    private string $output = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalAutoloadPaths = Yii::$app->params['moduleAutoloadPaths'];
        Yii::$app->params['moduleAutoloadPaths'][] = dirname(__DIR__, 2) . '/_data/ModuleDependencies';
        $this->originalOnlineModuleManager = $this->getOnlineModuleManagerProperty()->getValue(Yii::$app->getModule('marketplace'));

        foreach (self::FIXTURE_MODULE_IDS as $moduleId) {
            (new ModuleEnabled(['module_id' => $moduleId]))->save();
        }
    }

    protected function tearDown(): void
    {
        ModuleEnabled::deleteAll(['module_id' => self::FIXTURE_MODULE_IDS]);
        Yii::$app->params['moduleAutoloadPaths'] = $this->originalAutoloadPaths;
        $this->getOnlineModuleManagerProperty()->setValue(Yii::$app->getModule('marketplace'), $this->originalOnlineModuleManager);
        Yii::$app->cache->flush();

        parent::tearDown();
    }

    public function testUpdatesDependenciesFirst(): void
    {
        $order = $this->planUpdates([
            'dep-other' => $this->package('1.1.0', ['dep-child' => '1.1.0']),
            'dep-child' => $this->package('1.1.0', ['dep-parent' => '2.1.0']),
            'dep-parent' => $this->package('2.1.0'),
        ]);

        static::assertSame(['dep-parent', 'dep-child', 'dep-other'], $order);
        static::assertSame('', $this->output);
    }

    public function testSkipsUpdatesWhoseDependenciesStayUnmet(): void
    {
        $order = $this->planUpdates([
            'dep-other' => $this->package('1.1.0', ['dep-child' => '1.1.0']),
            'dep-child' => $this->package('1.1.0', ['dep-parent' => '2.1.0']),
            'dep-parent' => $this->package('2.0.9'),
        ]);

        static::assertSame(['dep-parent'], $order);
        static::assertStringContainsString(
            'Update of dep-child skipped: You must first upgrade the Dependency Parent module to version 2.1.0 or later.',
            $this->output,
        );
        static::assertStringContainsString(
            'Update of dep-other skipped: You must first upgrade the Dependency Child module to version 1.1.0 or later.',
            $this->output,
        );
    }

    public function testSkipsUpdatesThatCannotBeDownloaded(): void
    {
        $order = $this->planUpdates([
            'dep-child' => $this->package('1.1.0', ['dep-parent' => '2.1.0']),
            'dep-parent' => new ServerErrorHttpException('Download failed'),
        ]);

        static::assertSame([], $order);
        static::assertStringContainsString('Update of dep-parent failed: Download failed', $this->output);
        static::assertStringContainsString('Update of dep-child skipped', $this->output);
    }

    /**
     * @param array<string, array|\Throwable> $packages
     * @return string[]
     */
    private function planUpdates(array $packages): array
    {
        $onlineModuleManager = new class ($packages) extends OnlineModuleManager {
            public function __construct(private readonly array $packages)
            {
                parent::__construct();
            }

            public function getPackageModuleInfo(string $moduleId): array
            {
                if ($this->packages[$moduleId] instanceof \Throwable) {
                    throw $this->packages[$moduleId];
                }

                return $this->packages[$moduleId];
            }
        };
        $this->getOnlineModuleManagerProperty()->setValue(Yii::$app->getModule('marketplace'), $onlineModuleManager);

        $controller = new class ('module', Yii::$app) extends MarketplaceController {
            public string $output = '';

            public function stdout($string)
            {
                $this->output .= $string;
                return strlen($string);
            }
        };

        $order = (new ReflectionMethod($controller, 'planUpdates'))->invoke($controller, array_keys($packages));
        $this->output = $controller->output;

        return $order;
    }

    /**
     * @param array<string, string> $dependencies moduleId => minVersion
     */
    private function package(string $version, array $dependencies = []): array
    {
        return [
            'version' => $version,
            'humhub' => [
                'modules' => array_map(static fn(string $minVersion) => ['minVersion' => $minVersion], $dependencies),
            ],
        ];
    }

    private function getOnlineModuleManagerProperty(): ReflectionProperty
    {
        return new ReflectionProperty(Yii::$app->getModule('marketplace'), '_onlineModuleManager');
    }
}

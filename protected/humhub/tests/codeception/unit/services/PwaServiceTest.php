<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\services;

use Codeception\Test\Unit;
use humhub\services\PwaService;
use Yii;

/**
 * @since 1.20
 */
class PwaServiceTest extends Unit
{
    private $paramsBackup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paramsBackup = Yii::$app->params['pwa'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->paramsBackup === null) {
            unset(Yii::$app->params['pwa']);
        } else {
            Yii::$app->params['pwa'] = $this->paramsBackup;
        }
        parent::tearDown();
    }

    public function testIsEnabledDefaultsToTrue()
    {
        unset(Yii::$app->params['pwa']);

        $this->assertTrue(PwaService::isEnabled());
    }

    public function testIsEnabledFollowsParams()
    {
        Yii::$app->params['pwa']['enabled'] = false;

        $this->assertFalse(PwaService::isEnabled());
    }

    /**
     * The manifest doubles as the site icon registry, so it stays useful with PWA support off —
     * only the members that make the site installable are dropped.
     */
    public function testDisabledManifestKeepsIconsButDropsPwaMembers()
    {
        Yii::$app->params['pwa']['enabled'] = false;

        $manifest = PwaService::getManifest();

        $this->assertArrayHasKey('icons', $manifest);
        $this->assertArrayNotHasKey('display', $manifest);
        $this->assertArrayNotHasKey('start_url', $manifest);
        $this->assertArrayNotHasKey('theme_color', $manifest);
    }

    public function testEnabledManifestIsInstallable()
    {
        Yii::$app->params['pwa']['enabled'] = true;

        $manifest = PwaService::getManifest();

        $this->assertArrayHasKey('icons', $manifest);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame(Yii::$app->name, $manifest['name']);
        $this->assertSame(Yii::$app->name, $manifest['short_name']);
        $this->assertNotEmpty($manifest['start_url']);
    }
}

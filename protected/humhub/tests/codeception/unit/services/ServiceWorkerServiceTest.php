<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\services;

use Codeception\Test\Unit;
use humhub\events\ServiceWorkerScriptEvent;
use humhub\services\PwaService;
use humhub\services\ServiceWorkerService;
use yii\base\Event;
use yii\helpers\Url;

/**
 * @since 1.20
 */
class ServiceWorkerServiceTest extends Unit
{
    protected function tearDown(): void
    {
        Event::off(ServiceWorkerService::class, ServiceWorkerService::EVENT_BUILD_SCRIPT);
        parent::tearDown();
    }

    /**
     * The base script is what makes the offline fallback work: it caches the offline page
     * while the service worker installs.
     */
    public function testScriptCachesTheOfflinePage()
    {
        $script = ServiceWorkerService::buildScript();

        $this->assertStringContainsString(Url::to([PwaService::ROUTE_OFFLINE]), $script);
        $this->assertStringContainsString("addEventListener('install'", $script);
        $this->assertStringContainsString("caches.open('offline')", $script);
    }

    /**
     * EVENT_BUILD_SCRIPT is the public extension point modules like `fcm-push` rely on.
     * Without it their service worker logic silently disappears from `/sw.js`.
     */
    public function testModulesCanAppendTheirOwnScript()
    {
        Event::on(
            ServiceWorkerService::class,
            ServiceWorkerService::EVENT_BUILD_SCRIPT,
            function (ServiceWorkerScriptEvent $event) {
                $event->append('/* module script */');
            },
        );

        $script = ServiceWorkerService::buildScript();

        $this->assertStringContainsString("addEventListener('install'", $script);
        $this->assertStringEndsWith('/* module script */', $script);
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use humhub\events\ServiceWorkerScriptEvent;
use yii\base\Event;
use yii\helpers\Url;

/**
 * Assembles the JavaScript served as `/sw.js`.
 *
 * Modules add their own service worker logic by handling {@see self::EVENT_BUILD_SCRIPT}:
 *
 * ```php
 * // config.php
 * 'events' => [
 *     [ServiceWorkerService::class, ServiceWorkerService::EVENT_BUILD_SCRIPT, [Events::class, 'onBuildServiceWorkerScript']],
 * ],
 *
 * // Events.php
 * public static function onBuildServiceWorkerScript(ServiceWorkerScriptEvent $event): void
 * {
 *     $event->append($js);
 * }
 * ```
 *
 * @since 1.20
 */
class ServiceWorkerService
{
    /**
     * @event ServiceWorkerScriptEvent raised while the service worker script is assembled,
     * allowing modules to append their own JavaScript.
     */
    public const EVENT_BUILD_SCRIPT = 'buildScript';

    /**
     * @return string the complete service worker script
     */
    public static function buildScript(): string
    {
        $event = new ServiceWorkerScriptEvent(['script' => static::getInstallScript()]);
        Event::trigger(static::class, self::EVENT_BUILD_SCRIPT, $event);

        return $event->script;
    }

    /**
     * @return string script caching the offline page on service worker installation
     */
    private static function getInstallScript(): string
    {
        $offlinePageUrl = Url::to([PwaService::ROUTE_OFFLINE]);

        return <<<JS
            var OFFLINE_PAGE_URL = '{$offlinePageUrl}';

            self.addEventListener('install', function (event) {
                console.log('********** The service worker is being installed.');

                // Store "Offline" page
                var offlineRequest = new Request(OFFLINE_PAGE_URL, {init: {
                    credentials: 'omit'
                }});

                event.waitUntil(
                    fetch(offlineRequest).then(function (response) {
                        return caches.open('offline').then(function (cache) {
                                console.log('[oninstall] Cached offline page', response.url);
                                return cache.put(offlineRequest, response);
                            });
                    })
                );
            });

JS;
    }
}

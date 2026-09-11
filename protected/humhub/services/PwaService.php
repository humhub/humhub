<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use humhub\components\View;
use Yii;
use yii\helpers\Url;

/**
 * Service for the Progressive Web App support: the web app manifest, the head tags that
 * make HumHub installable and the registration of the service worker.
 *
 * The manifest is served independently of {@see self::isEnabled()} because it also carries
 * the site icons; only the PWA specific members are omitted when PWA support is disabled.
 *
 * @since 1.20
 */
class PwaService
{
    /**
     * Public URLs. These end up in browser manifest registrations and in the service worker
     * cache of already installed apps and must therefore stay stable.
     */
    public const URL_MANIFEST = 'manifest.json';
    public const URL_SERVICE_WORKER = 'sw.js';
    public const URL_OFFLINE = 'offline.pwa.html';

    /**
     * Internal routes the public URLs are mapped to.
     */
    public const ROUTE_MANIFEST = '/pwa/manifest';
    public const ROUTE_SERVICE_WORKER = '/pwa/service-worker';
    public const ROUTE_OFFLINE = '/pwa/offline';

    /**
     * Cache key holding the version of the service worker script, used to bust the browser
     * cache of the registered worker.
     */
    private const CACHE_KEY_VERSION = 'service-worker-cache-id';

    /**
     * @return bool whether Progressive Web App support is enabled
     */
    public static function isEnabled(): bool
    {
        return (bool)(Yii::$app->params['pwa']['enabled'] ?? true);
    }

    /**
     * @return array the web app manifest
     */
    public static function getManifest(): array
    {
        $manifest = [
            'gcm_sender_id' => '103953800507',
            'icons' => static::getIcons(),
        ];

        if (!static::isEnabled()) {
            return $manifest;
        }

        return array_merge($manifest, [
            'display' => 'standalone',
            'start_url' => Url::base(true),
            'short_name' => Yii::$app->name,
            'name' => Yii::$app->name,
            'background_color' => Yii::$app->view->theme->variable('primary'),
            'theme_color' => Yii::$app->view->theme->variable('primary'),
        ]);
    }

    /**
     * Registers the mobile app related head tags and, when enabled, the service worker.
     */
    public static function registerHeadTags(View $view): void
    {
        $view->registerMetaTag(['name' => 'theme-color', 'content' => $view->theme->variable('primary')]);
        $view->registerMetaTag(['name' => 'application-name', 'content' => Yii::$app->name]);

        // Apple/iOS headers
        // https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html
        $view->registerMetaTag(['name' => 'apple-mobile-web-app-title', 'content' => Yii::$app->name]);
        $view->registerMetaTag(['name' => 'mobile-web-app-capable', 'content' => 'yes']);
        $view->registerMetaTag(['name' => 'apple-mobile-web-app-status-bar-style', 'content' => $view->theme->variable('primary')]);

        $view->registerLinkTag(['rel' => 'manifest', 'href' => Url::to([static::ROUTE_MANIFEST])]);

        if (static::isEnabled()) {
            static::registerServiceWorker($view);
        }
    }

    private static function getIcons(): array
    {
        $icons = [];

        foreach ([48, 72, 96, 192, 512] as $size) {
            $src = Yii::$app->img->icon->getUrl(['square' => $size]);
            if (!empty($src)) {
                $icons[] = [
                    'src' => $src,
                    'type' => 'image/png',
                    'sizes' => $size . 'x' . $size,
                ];
            }
        }

        return $icons;
    }

    private static function registerServiceWorker(View $view): void
    {
        $version = Yii::$app->cache->getOrSet(self::CACHE_KEY_VERSION, fn() => time());
        $serviceWorkerUrl = Url::to([static::ROUTE_SERVICE_WORKER, 'v' => $version]);
        $rootPath = Yii::getAlias('@web') . '/';

        $view->registerJs(<<<JS
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('$serviceWorkerUrl', { scope: '$rootPath' })
                    .then(function (registration) {
                        if (typeof afterServiceWorkerRegistration === "function") {
                            // Allow Modules like `fcm-push` to register after registration
                            afterServiceWorkerRegistration(registration);
                        }
                    })
            }
JS
            , View::POS_READY, 'serviceWorkerInit');
    }
}

# Module Migration — Version 1.20

Breaking changes, new APIs and deprecations of the 1.20 release cycle.

- The PWA part of the `web` module moved into the core namespace and was consolidated into a
  single controller and two services. The public URLs `/manifest.json`, `/sw.js` and
  `/offline.pwa.html` are unchanged — installed apps keep working — but every class and internal
  route behind them is new.

  | Removed | Replacement |
  |---|---|
  | `humhub\modules\web\pwa\controllers\ManifestController` | `humhub\controllers\PwaController::actionManifest()` |
  | `humhub\modules\web\pwa\controllers\ServiceWorkerController` | `humhub\controllers\PwaController::actionServiceWorker()` |
  | `humhub\modules\web\pwa\controllers\OfflineController` | `humhub\controllers\PwaController::actionOffline()` |
  | `humhub\modules\web\pwa\widgets\LayoutHeader` | `humhub\services\PwaService::registerHeadTags()` |
  | Route `web/pwa-manifest/index` | `pwa/manifest` (`PwaService::ROUTE_MANIFEST`) |
  | Route `web/pwa-service-worker/index` | `pwa/service-worker` (`PwaService::ROUTE_SERVICE_WORKER`) |
  | Route `web/pwa-offline/index` | `pwa/offline` (`PwaService::ROUTE_OFFLINE`) |
  | i18n category `WebModule.pwa` | `base` |

  - **Extending the service worker changed.** `ServiceWorkerController::$baseJs` and
    `$additionalJs` are gone; the controller is no longer an extension point. Modules now handle
    `humhub\services\ServiceWorkerService::EVENT_BUILD_SCRIPT` and append to the
    `humhub\events\ServiceWorkerScriptEvent` they receive:

    ```php
    // config.php — before
    ['humhub\modules\web\pwa\controllers\ServiceWorkerController', Controller::EVENT_INIT, [Events::class, 'onServiceWorkerControllerInit']],

    // config.php — after
    [ServiceWorkerService::class, ServiceWorkerService::EVENT_BUILD_SCRIPT, [Events::class, 'onBuildServiceWorkerScript']],
    ```

    ```php
    // Events.php — before
    public static function onServiceWorkerControllerInit($event): void
    {
        $event->sender->additionalJs .= $js;
    }

    // Events.php — after
    public static function onBuildServiceWorkerScript(ServiceWorkerScriptEvent $event): void
    {
        $event->append($js);
    }
    ```

    **Warning:** an unmigrated module does not fail — its handler is registered on a class that
    no longer exists and is simply never called, so the module's service worker logic silently
    disappears from `/sw.js`. Audit every event handler registered on a `humhub\modules\web\pwa`
    class.

  - The manifest lost its (undocumented) extension point along the way: `ManifestController`
    exposed a public `$manifest` array and fired `Controller::EVENT_INIT`, so a module could
    append members to it. `PwaService::getManifest()` has no equivalent event. No known module
    used it; if you need one, please open an issue.

  - **Removed** `humhub\modules\web\Module::$enableServiceWorker`. The flag never only disabled
    the service worker — it also stripped `display`, `start_url` and the color members from the
    manifest — and is replaced by the application parameter `pwa.enabled`:

    ```php
    // config/common.php — before
    'modules' => ['web' => ['enableServiceWorker' => false]],

    // config/common.php — after
    'params' => ['pwa' => ['enabled' => false]],
    ```

    Read it through `humhub\services\PwaService::isEnabled()`. As an environment variable:
    `HUMHUB_CONFIG__PARAMS__PWA__ENABLED=0`.

  - The `web` module itself still exists and continues to provide the security headers and CSP
    handling; only its PWA part moved.

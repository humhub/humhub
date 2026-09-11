# Module Migration — Version 1.20

Breaking changes, new APIs and deprecations of the 1.20 release cycle.

- **HumHub now serves from a `public/` directory.** The web server's document root belongs on
  `<installation root>/public`; everything beside it - `protected/`, `uploads/`, `themes/`, the
  Composer and npm metadata, the `.env` file - is meant to stay out of reach of the web server.
  The entry script is `public/index.php`.

  `@webroot` follows the document root, which is what Yii assigns it during bootstrap anyway, and
  the new `@root` alias names the installation root:

  | Before | After |
  |---|---|
  | `@webroot` (installation root) | `@root` |
  | `@webroot/uploads` | `@root/uploads` |
  | `@webroot/themes` | `@themes` (unchanged, now resolving outside the document root) |
  | `@webroot/assets` | unchanged - it resolves into `public/assets` |

  Both `.htaccess` files are now shipped ready to use instead of as `.htaccess.dist` templates -
  there is no renaming step any more, and forgetting it can no longer leave the installation
  directory exposed. The one in the installation directory maps every request into `public/`, so a
  document root that cannot be moved is protected too. Local edits to either file are overwritten
  on update; host-specific configuration belongs in the virtual host.

  Audit every `@webroot` in your module. If it points at something the web server should serve,
  it is still correct; if it points at anything else, it now names a directory below `public/`
  that does not exist.

  - **Themes are unaffected unless they opt out of publishing.** `Theme::$publishResources`
    defaults to `true`, so theme resources are published into the assets mount and reachable as
    before. A theme configured with `publishResources = false` falls back to `@web/themes/...`,
    which no longer resolves to anything the web server serves.

  - **The old entry script keeps working for now.** An `index.php` remains in the installation
    root, marks itself deprecated through the `HUMHUB_LEGACY_ENTRY_SCRIPT` environment variable
    and delegates to `public/index.php` without redirecting. While it is in use, `@web` is
    prefixed with the path from the entry script to `public/`, so assets are loaded from
    `/public/assets/...`. Both the deprecated entry script and an installation root that is
    reachable over HTTP are reported under Administration -> Information -> Prerequisites and
    in the incomplete-setup warning on the admin dashboard. The script will be removed in a
    future version.

    Legacy mode is never inferred from paths - managed hosting runs its own entry scripts and
    path layouts, where such a guess would be wrong. Only the shipped script declares itself.
    For layouts in which `public/` is not below the entry script, `HUMHUB_PUBLIC_URL` sets the
    URL the document root is reachable under.

- The `web` module is gone. Its PWA part moved in the change above; the security part - the
  headers and the Content Security Policy - is now applied by `humhub\components\Response`
  itself and configured on that component.

  | Removed | Replacement |
  |---|---|
  | `humhub\modules\web\Module` (module id `web`) | - |
  | `humhub\modules\web\Events` | - |
  | `humhub\modules\web\security\helpers\Security` | `humhub\components\Response::getNonce()`, `humhub\helpers\Html::getNonce()` |
  | `humhub\modules\web\security\helpers\CSPBuilder` | - (the policy is configured as a plain header string) |
  | `humhub\modules\web\security\models\SecuritySettings` | `humhub\components\Response::$defaultHeaders` |
  | `humhub\modules\web\security\controllers\ReportController` | `humhub\controllers\CspReportController` |
  | Route `web/security-report` | `csp-report/index` |
  | `Security::CSP_VIOLATION_RELOAD_INTERVAL` | - |
  | i18n category `WebModule.base` | - |

  - **Configuration moved** from the module to the response component, as a flat map of header
    name to value. The `csp` section with its per-directive arrays, the `csp-report-only`
    section and the separate `nonce` switch are all gone:

    ```php
    // config/web.php - before
    'modules' => [
        'web' => [
            'security' => [
                'headers' => ['X-Frame-Options' => 'sameorigin', ...],
                'csp' => ['nonce' => true],
            ],
        ],
    ],

    // config/web.php - after
    'components' => [
        'response' => [
            'defaultHeaders' => [
                'X-Frame-Options' => 'sameorigin',
                'Content-Security-Policy' => "... script-src {{ nonce }} 'self' ...",
            ],
        ],
    ],
    ```

    The map is not limited to security headers - any header can be configured there. Entries are
    applied as **defaults**: a header an action set itself is left alone.

    A header value may contain `{{ nonce }}`, replaced with the nonce of the current session,
    and `{{ reportUri }}`, replaced with the URL of the report endpoint. **A header containing
    `{{ nonce }}` is what turns nonce support on** - there is no separate switch any more, so
    the two can no longer contradict each other. Reports are only logged when a header actually
    points at the endpoint through `{{ reportUri }}`.

    To send a policy in report-only mode, add `Content-Security-Policy-Report-Only` to the same
    map; both header names are treated alike.

  - **The policy now reaches every HTML response.** It used to be applied on
    `Controller::EVENT_BEFORE_ACTION` for non-AJAX requests, with an `instanceof` exclusion list,
    which left error pages without a policy - the error handler clears the response before
    rendering one. Headers are applied in `Response::prepare()` instead, and whether a
    `Content-Security-Policy` is sent is decided by the response `Content-Type`: HTML documents
    get one, JSON and JavaScript responses do not. A module that renders HTML from an AJAX
    action now gets a policy where it previously got none; inline scripts in it need a nonce,
    which `humhub\helpers\Html` applies to every `<script>` tag it renders.

  - **Removed the automatic page reload on CSP violation** (`CSP_VIOLATION_RELOAD_INTERVAL` and
    the `securitypolicyviolation` listener in `humhub.client.js`). It reloaded the page - losing
    unsaved input - on *any* `script-src` violation, not only on the obsolete nonce it was meant
    to paper over. Its original trigger was fixed at the root in #7312. The nonce is no longer
    reset on login either, so it stays valid for the lifetime of a session; this is what made
    already-open tabs break after a re-login elsewhere.

  - `humhub\helpers\Html::nonce()` and `Html::setNonce()` are unchanged. New:
    `Html::getNonce()` returning the raw value, and `null` outside a web request.

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

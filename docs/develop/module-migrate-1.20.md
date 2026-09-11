# Module Migration — Version 1.20

Breaking changes, new APIs and deprecations of the 1.20 release cycle.

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

- **The form part of the `ui` module moved into the core namespace.** 1.19 had already moved
  `ActiveForm`, `ActiveField`, `ContentHiddenCheckbox`, `ContentVisibilitySelect` and
  `SortOrderField` out of `humhub\modules\ui\form\widgets` into `humhub\widgets\form`; the
  remaining widgets follow them now, which also removes the last reason for core classes such as
  `humhub\widgets\form\ActiveField` and `humhub\widgets\bootstrap\FormTabs` to reach back into a
  module.

  | Before | After |
  |---|---|
  | `humhub\modules\ui\form\widgets\BasePicker` | `humhub\widgets\form\BasePicker` |
  | `humhub\modules\ui\form\widgets\JsInputWidget` | `humhub\widgets\form\JsInputWidget` |
  | `humhub\modules\ui\form\widgets\MultiSelect` | `humhub\widgets\form\MultiSelect` |
  | `humhub\modules\ui\form\widgets\DatePicker` | `humhub\widgets\form\DatePicker` |
  | `humhub\modules\ui\form\widgets\TimePicker` | `humhub\widgets\form\TimePicker` |
  | `humhub\modules\ui\form\widgets\DurationPicker` | `humhub\widgets\form\DurationPicker` |
  | `humhub\modules\ui\form\widgets\IconPicker` | `humhub\widgets\form\IconPicker` |
  | `humhub\modules\ui\form\widgets\CodeMirrorInputWidget` | `humhub\widgets\form\CodeMirrorInputWidget` |
  | `humhub\modules\ui\form\assets\CodeMirrorAssetBundle` | `humhub\assets\CodeMirrorAssetBundle` |
  | `humhub\modules\ui\form\interfaces\TabbedFormModel` | `humhub\interfaces\TabbedFormModel` |

  - **Nothing breaks in 1.20.** Every old name stays available as a deprecated subclass (and, for
    `TabbedFormModel`, a deprecated interface extending the new one), so existing modules keep
    working. **The shims are removed in 1.21** — migrate during the 1.20 cycle.

  - Note for `DatePicker`, `TimePicker` and `DurationPicker`: these were moved *into*
    `humhub\modules\ui\form\widgets` from `humhub\widgets` in 1.19. A module that has not migrated
    yet can go straight from `humhub\widgets\DatePicker` to `humhub\widgets\form\DatePicker`.

  - The deprecated `CodeMirrorAssetBundle` is a subclass, so Yii registers it under its own name.
    A page that registers both the old and the new bundle emits the CodeMirror script tags twice.
    Register only one of them.

  - **Removed** `humhub\modules\ui\form\validators\IconValidator` without replacement. It was
    added in a single commit, never used by the core or any known module, and validated against
    `Icon::$names` — three lines that are easier written inline than kept as public API.

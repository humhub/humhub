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
- **Removed the unused `ContainerImageSet` widget** and the asset bundle, JavaScript and CSS that
  belonged to it. The widget was added in 2019 and never rendered anywhere — neither by the core
  nor by any known module — so it is dropped rather than carried along as the `ui` module is taken
  apart.

  | Removed | Replacement |
  |---|---|
  | `humhub\modules\ui\content\widgets\ContainerImageSet` | - |
  | `humhub\modules\ui\content\assets\UiImageSetAsset` | - |
  | JS module `ui.imageset` | - |
  | CSS classes `.ui-imageset-*` | - |

  A module that renders its own markup with those CSS classes has to ship the styles itself; a
  module that extends the widget has to bring its own copy.

- **The icon part of the `ui` module moved into the core namespace.** `Icon` joins the other core
  widgets, and the three provider classes behind it — which no module and no other core class ever
  referenced — become components.

  | Before | After |
  |---|---|
  | `humhub\modules\ui\icon\widgets\Icon` | `humhub\widgets\Icon` |
  | `humhub\modules\ui\icon\components\IconFactory` | `humhub\components\icon\IconFactory` |
  | `humhub\modules\ui\icon\components\IconProvider` | `humhub\components\icon\IconProvider` |
  | `humhub\modules\ui\icon\components\FontAwesomeIconProvider` | `humhub\components\icon\FontAwesomeIconProvider` |
  | i18n category `UiModule.icon` | `base` |

  - **Nothing breaks in 1.20.** Every old name stays available as a deprecated subclass, and
    `IconProvider` as a deprecated interface extending the new one. **The shims are removed in
    1.21** — `Icon::get()` alone appears well over a hundred times across the module ecosystem, so
    migrate during the 1.20 cycle. In most modules it is one `use` line per file.

  - **The icon alias map moved out of the module into an application parameter.** It used to live
    on the module as `iconAlias` and was read through `Module::getModuleInstance()->getIconAlias()`
    — the last thing in the core that needed the `ui` module class at all:

    ```php
    // config/common.php - before
    'modules' => ['ui' => ['iconAlias' => ['edit' => 'pen']]],

    // config/common.php - after
    'params' => ['icon' => ['alias' => ['edit' => 'pen']]],
    ```

    As an environment variable: `HUMHUB_CONFIG__PARAMS__ICON__ALIAS__EDIT=pen`. The defaults are
    unchanged and shipped in `protected/humhub/config/common.php`; resolution is now
    `humhub\widgets\Icon::resolveAlias()`, and a name the map does not cover is still used as
    given.

    **`humhub\modules\ui\Module::$iconAlias` and `getIconAlias()` are gone.** An installation that
    configured `modules.ui.iconAlias` has to move the values, otherwise the setting is rejected as
    an unknown module property.

  - The one string of the `UiModule.icon` category — a log warning about an unregistered icon
    provider — moved to `base` with its translations in the 27 languages that had one.

- **The menu part of the `ui` module moved into the core namespace**, flattened into a single
  namespace: the entry classes and the menu widgets that carry them now live side by side.

  | Before | After |
  |---|---|
  | `humhub\modules\ui\menu\widgets\Menu` | `humhub\widgets\menu\Menu` |
  | `humhub\modules\ui\menu\widgets\TabMenu` | `humhub\widgets\menu\TabMenu` |
  | `humhub\modules\ui\menu\widgets\SubTabMenu` | `humhub\widgets\menu\SubTabMenu` |
  | `humhub\modules\ui\menu\widgets\LeftNavigation` | `humhub\widgets\menu\LeftNavigation` |
  | `humhub\modules\ui\menu\widgets\DropdownMenu` | `humhub\widgets\menu\DropdownMenu` |
  | `humhub\modules\ui\menu\MenuEntry` | `humhub\widgets\menu\MenuEntry` |
  | `humhub\modules\ui\menu\MenuLink` | `humhub\widgets\menu\MenuLink` |
  | `humhub\modules\ui\menu\DropdownDivider` | `humhub\widgets\menu\DropdownDivider` |
  | `humhub\modules\ui\menu\WidgetMenuEntry` | `humhub\widgets\menu\WidgetMenuEntry` |

  - **Nothing breaks in 1.20.** Every old name stays available as a deprecated subclass. **The
    shims are removed in 1.21** — migrate during the 1.20 cycle.

  - **Event handlers are unaffected.** All five menu widgets are abstract and can never be an
    event sender; handlers are always registered on a concrete menu such as
    `humhub\modules\admin\widgets\AdminMenu`, whose name does not change. Where `config.php`
    mentions `Menu` at all it is as a holder of `Menu::EVENT_INIT` or `Menu::EVENT_RUN`, and the
    deprecated subclass inherits both constants, so those registrations keep working untouched.

  - **Theme view overrides have to be moved, and this is the one change here that fails
    silently.** Themed views are resolved by file path, not by class name, so a shim cannot cover
    them: an override left at the old path is simply never applied again — no error, no log entry,
    the core view is rendered instead.

    | Before | After |
    |---|---|
    | `themes/<theme>/views/ui/menu/widgets/views/tab-menu.php` | `themes/<theme>/views/humhub/widgets/menu/views/tab-menu.php` |
    | `themes/<theme>/views/ui/menu/widgets/views/sub-tab-menu.php` | `themes/<theme>/views/humhub/widgets/menu/views/sub-tab-menu.php` |
    | `themes/<theme>/views/ui/menu/widgets/views/left-navigation.php` | `themes/<theme>/views/humhub/widgets/menu/views/left-navigation.php` |
    | `themes/<theme>/views/ui/menu/widgets/views/dropdown-menu.php` | `themes/<theme>/views/humhub/widgets/menu/views/dropdown-menu.php` |

    Check every theme you maintain for a `views/ui/menu/` directory.

  - A menu that points `$template` at the shipped views directly has to follow the alias:
    `@ui/menu/widgets/views/dropdown-menu.php` becomes
    `@humhub/widgets/menu/views/dropdown-menu.php`.
- **Removed the unused `ItemDrop` model** for drag-and-drop reordering.

  | Removed | Replacement |
  |---|---|
  | `humhub\modules\ui\helpers\models\ItemDrop` | - |

  It was added in 1.4 and never used by the core or by any known module, and as shipped it could
  not have worked: `save()` calls `$this->moveItemIndex()`, a method the class does not define —
  the resulting `Error` was caught by its own `catch (Throwable)`, logged, and reported as a
  failed save. Two more leftovers point the same way: `run()` and `loadModel()` read `$this->id`
  while the class declares `$modelId`, and `getSortOrder()` uses variable-variable syntax
  (`$model->${$this->sortOrderField}`) where a property access was meant.

  The class was extracted from the `wiki` and `infoscreen` modules, where the method is called
  `moveItemIndex()`; the rename to `run()` never reached the caller. Both modules — and `tasks`
  and `meeting` — still carry their own reordering models and are unaffected. A module that does
  extend this class needs to bring its own copy; correcting the four defects above first is
  advisable.

- **The remaining widgets of the `ui` module moved into the core namespace.**

  | Before | After |
  |---|---|
  | `humhub\modules\ui\widgets\BaseImage` | `humhub\widgets\BaseImage` |
  | `humhub\modules\ui\widgets\CropImage` | `humhub\widgets\CropImage` |
  | `humhub\modules\ui\widgets\CounterSet` | `humhub\widgets\CounterSet` |
  | `humhub\modules\ui\widgets\CounterSetItem` | `humhub\widgets\CounterSetItem` |
  | `humhub\modules\ui\widgets\DirectoryFilters` | `humhub\widgets\DirectoryFilters` |

  - **Nothing breaks in 1.20.** Every old name stays available as a deprecated subclass,
    `DirectoryFilters` as a deprecated abstract one. **The shims are removed in 1.21.**

  - The three views move with them, so a widget pointing `$template` or `render()` at them has to
    follow: `@ui/widgets/views/counterSetHeader` and
    `@humhub/modules/ui/widgets/views/directoryFilter[s]` become `@humhub/widgets/views/…`. No
    theme in the ecosystem overrides these views.

  - `DirectoryFilters` stays a top-level widget rather than moving next to the filter widgets of
    `humhub\modules\ui\filter`. The two are unrelated implementations: `DirectoryFilters` extends
    `humhub\components\Widget` directly and drives the directory pages (spaces, people, content
    search, marketplace), while the `filter` widgets build the stream filter panel. The `filter`
    part of the module is untouched for now.

- **The filter part of the `ui` module moved into the core namespace.** The widgets keep their
  own namespace next to the form and menu widgets; the two abstract models join
  `humhub\models`, and the asset joins the other core bundles.

  | Before | After |
  |---|---|
  | `humhub\modules\ui\filter\widgets\FilterInput` | `humhub\widgets\filter\FilterInput` |
  | `humhub\modules\ui\filter\widgets\FilterBlock` | `humhub\widgets\filter\FilterBlock` |
  | `humhub\modules\ui\filter\widgets\FilterPanel` | `humhub\widgets\filter\FilterPanel` |
  | `humhub\modules\ui\filter\widgets\FilterNavigation` | `humhub\widgets\filter\FilterNavigation` |
  | `humhub\modules\ui\filter\widgets\CheckboxFilterInput` | `humhub\widgets\filter\CheckboxFilterInput` |
  | `humhub\modules\ui\filter\widgets\CheckboxListFilterInput` | `humhub\widgets\filter\CheckboxListFilterInput` |
  | `humhub\modules\ui\filter\widgets\RadioFilterInput` | `humhub\widgets\filter\RadioFilterInput` |
  | `humhub\modules\ui\filter\widgets\DropdownFilterInput` | `humhub\widgets\filter\DropdownFilterInput` |
  | `humhub\modules\ui\filter\widgets\TextFilterInput` | `humhub\widgets\filter\TextFilterInput` |
  | `humhub\modules\ui\filter\widgets\DatePickerFilterInput` | `humhub\widgets\filter\DatePickerFilterInput` |
  | `humhub\modules\ui\filter\widgets\PickerFilterInput` | `humhub\widgets\filter\PickerFilterInput` |
  | `humhub\modules\ui\filter\models\Filter` | `humhub\models\filter\Filter` |
  | `humhub\modules\ui\filter\models\QueryFilter` | `humhub\models\filter\QueryFilter` |
  | `humhub\modules\ui\filter\assets\FilterAsset` | `humhub\assets\FilterAsset` |

  - **Nothing breaks in 1.20.** Every old name stays available as a deprecated subclass. **The
    shims are removed in 1.21** — migrate during the 1.20 cycle.

  - The filter machinery landed in the core rather than in the `stream` module even though the
    stream filter panel is its only consumer inside the core. It carries no knowledge of streams,
    and two of its outside consumers are not streams at all: the `mail` inbox sidebar builds on
    `QueryFilter`, and the task search page of `tasks` extends four of the input widgets. Filing
    it under `stream` would have made those depend on the stream module.

  - **Removed three views that nothing rendered:** `streamTopicPicker`, `topicFilterBlock` and
    `contentTypeFilterBlock`. They were the only place where the filter code reached into the
    `topic` and `content` modules, and no core class or known module referenced them — neither
    literally nor through the `view` property of a filter widget.
    `WallStreamFilterNavigation` builds its topic and content-type blocks from ordinary
    `PickerFilterInput` instances. A module that set one of the three as its `view` has to bring
    its own copy.

  - `humhub.ui.filter.js` moved to `protected/humhub/resources/js/humhub/` and `FilterAsset`
    follows the core convention (`@humhub/resources`). The JavaScript module id `ui.filter` is
    unchanged, so `require('ui.filter')` keeps working, and a module that registers `FilterAsset`
    needs no change beyond the new class name.

- **The `ui` module is gone.** Its seven folders moved into the core namespace over the changes
  above; what is left of the module itself — `Module`, `Events`, `config.php`, `module.json`, the
  message catalogues and the test suite — is removed with this change.

  | Removed | Replacement |
  |---|---|
  | `humhub\modules\ui\Module` (module id `ui`) | - |
  | `humhub\modules\ui\Events` | - |
  | Alias `@ui` | `@humhub` |
  | i18n category `UiModule.base` | `base` |
  | i18n category `UiModule.form` | `base` |
  | i18n category `UiModule.markdownEditor` | `base` |

  - **The deprecated class names keep working.** `humhub\` is autoloaded from the `@humhub` alias,
    not through a module registration, so the 42 deprecated subclasses under
    `humhub\modules\ui\{filter,form,icon,menu,widgets}` still resolve with the module gone. They
    are removed in 1.21, and the directory disappears with them.

  - **Configuration under `modules.ui` is no longer accepted** and raises an unknown-property
    error. The only setting that ever lived there was `iconAlias`, replaced by the `icon.alias`
    application parameter in the icon change above.

  - `Yii::getAlias('@ui/…')` now throws. No known module used it.

  - The 39 strings of the three categories moved into `base` with their translations. Where a
    string already existed in `base` the existing translation was kept; this affects one string,
    `Collapse`, whose wording now follows the core catalogue in Arabic, French, Italian, Dutch,
    Slovak and Swedish.

  - The module's test suite moved into the core suite, so `grunt test --module=ui` no longer
    exists. `ThemeHelperTest` and `TabbedFormTest` keep their names under
    `humhub\tests\codeception\unit\{helpers,widgets}`; the module's `ThemeTest` became
    `ThemePublishTest` because the core suite already has a `ThemeTest` covering path mapping, and
    its single SCSS test joined the existing `ScssHelperTest`.

- **`humhub\assets\FilterAsset` no longer depends on `humhub\modules\topic\assets\TopicAsset`.**
  The dependency moved to `humhub\modules\stream\assets\StreamAsset`, where it belongs:
  `humhub.ui.filter.js` requires only `ui.widget` and `util` and has no relationship to topics,
  while it is `WallStreamFilterNavigation` — in the stream module — that configures a
  `PickerFilterInput` with `TopicPicker`.

  Nothing changes about the scripts a page loads: `TopicAsset` is registered by the core bundle
  directly, and the transitive dependency closure of `CoreBundleAsset` is identical before and
  after. Only a module that registers `FilterAsset` on its own *and* renders a topic picker has
  to register `TopicAsset` itself now.

- Added a **describable menu entry** API (`humhub\widgets\menu\MenuEntry::describe()`,
  the `humhub\widgets\menu\DescribableWidget` interface) plus the
  `humhub\modules\content\vue\ContentControls` island and its
  `GET /api/v2/content/<id>/controls` endpoint — the content context menu (`WallEntryControls`)
  rendered by a client instead of the server. Purely additive: `WallEntryControls::EVENT_INIT`
  is unchanged, and it is still the way to contribute an entry.
  - `WallEntryControlLink` implements `DescribableWidget`, so every control link extending it
    (`EditPageLink` in wiki, `ShareLink` in share-between-humhub, `ContentTopicButton` in core)
    is described without any module change.
  - **Deprecated**: contributing a menu entry whose widget cannot describe itself. Such an
    entry is still rendered server-side and delivered as raw HTML, so nothing breaks today,
    but it cannot be conditioned, overridden or removed by a client, and every delivery logs
    a warning naming the widget class. A subclass of `WallEntryControlLink` that overrides
    `renderLink()` is deliberately in this group unless it also overrides
    `describeMenuEntry()` — describing it from the base class' properties would produce an
    empty label or a dead `#` link. See `docs/develop/ui-js-vuejs-extensions.md`,
    "Server-described entries and `ContentControls`".
  - The Vue `DropdownMenu` entry descriptor grew `url`, `htmlOptions`, `divider` and `html`.
    Existing entries are unaffected.
  - `DropdownMenu` grew a `rootClass` prop and a `toggle` slot, both defaulting to the previous
    markup. Needed because its root is hard-coded `.nav nav-pills preferences`, which
    `_nav.scss` fills with the primary colour and positions absolutely — correct for a
    content-controls menu, wrong for a labelled dropdown button.
  - **Fixed**: `_list.scss` no longer colours `a.dropdown-item` inside an `.hh-list` row. Its
    rule tied with `_nav.scss`'s menu-item colour (both 0,2,2) and won on import order, so a
    dropdown rendered inside a list was painted with the list's text colour on the menu's own
    background. Only affects anchors carrying `.dropdown-item`; ordinary row links are
    unchanged.

- Added the **Vue.js island layer** (`humhub.vue` client registry/mounter, `humhub\widgets\VueComponent`,
  `grunt build-vue` tooling) — see `docs/develop/ui-js-vuejs.md`. Purely additive; the existing
  `humhub.module` JS system is unaffected.
  - Also added the standalone core JS module `url` (`humhub.url.js`) — the client-side counterpart
    of `yii\helpers\Url::to()` for default-routed endpoints, usable from any `humhub.module()` via
    `require('url').to(route, params)`. Purely additive; `@humhub/vue`'s `url()` now delegates to it.
  - Added `humhub\components\assets\VueAssetBundle`, the base class of a module's `*VueAsset`
    bundle: it derives source path and artifact from `$moduleId` and always depends on
    `humhub\assets\CoreVueAsset`, so a module lists only the `*VueAsset` bundles of the modules
    whose components it nests. Added `humhub\widgets\VueWidget`, the base class of a PHP widget
    rendering an island (`getProps()`/`getOptions()`/`getPlaceholder()` over `VueComponent`).
    Both purely additive; see `docs/develop/ui-js-vuejs-components.md`.
  - **Removed**: the `humhub\widgets\views\statusBar.php` view — `humhub\widgets\StatusBar` renders
    the `StatusBar` island directly. A theme overriding that view overrides the widget or the Vue
    component instead.
  - The publish excludes for `@humhub/resources` bundles (`scss/`, `.gitignore` — introduced
    with #8277) were never applied at runtime due to an alias comparison bug and are now
    active; `build/` is deliberately **not** excluded because the compiled `humhub-app.css`
    references it via relative `url()`.
  - **Removed**: the client-side `like` JS module (`humhub.like.js`, `like.toggleLike`) and
    `humhub\modules\like\assets\LikeAsset` — the like link is now a self-contained Vue island
    (`humhub\modules\like\vue\LikeButton.vue`, `LikeVueAsset`) that builds its own URLs and
    labels client-side. The `humhub:like:liked` DOM event is still fired on like, unchanged.
    The `likeLinkContainer_<id>` wrapper element id no longer exists — the like-user-list modal
    covers the same "who liked this" info the old title tooltip provided. Known affected: a CSS
    selector on `data-action-click="like.toggleLike"` in `cuzy-app/clean-theme` (dead selector,
    cosmetic only — see module-search results in the implementing PR).
  - Added `UserImage` (`<user-image>`) to the user module's Vue component set
    (`protected/humhub/modules/user/vue/`, `humhub\modules\user\assets\UserVueAsset`, see
    `docs/develop/ui-js-vuejs-components.md`) — a user's profile image, the Vue analog of
    `user\widgets\Image`. Purely additive; `CommentEntry.vue`'s hand-rolled avatar markup
    (img + online-status overlay) now uses it (`<UserImage v-bind="comment.author" />`),
    depending on `UserVueAsset` the same way it already depends on `LikeVueAsset` for
    `<LikeButton>`.
  - **Removed**: `humhub\modules\like\services\LikeService::generateLikeTitleText()` — it built the
    old title-tooltip text, which the Vue-island like link no longer renders; no known callers.
  - **Removed**: the `humhub\modules\like\widgets\views\likeLink.php` view file —
    `humhub\modules\like\widgets\LikeLink::run()` now returns the `LikeButton` island directly with
    no PHP view in between. Theme overrides of `like/widgets/views/likeLink.php` no longer apply;
    override the `LikeLink` widget or the `LikeButton` Vue component instead. Guests now see the
    like **count** again (non-interactive, no like/unlike controls) — restoring the pre-Vue
    behavior that an earlier iteration of this island had temporarily dropped; guest state
    (login-modal link vs. count) is driven client-side via the `isGuest`/`loginUrl` keys of the
    `user` `registerJsConfig` section (see `docs/develop/ui-js-vuejs.md`). `/like/like/info` is now
    guest-accessible (`guestAllowedActions`); content visibility is still enforced by
    `Content::canView()` in `LikeController::beforeAction()`.
  - Added core `UiModal` (`<ui-modal>`, `protected/humhub/vue/UiModal.vue`) — the first
    native, Vue-owned modal (reuses the legacy `.modal`/`.modal-dialog`/`.modal-backdrop`
    Bootstrap 5 markup/CSS so it looks identical, but owns open/close/backdrop/keyboard/
    focus/scroll-lock itself instead of wrapping `bootstrap.Modal`); see
    `docs/develop/ui-js-vuejs-components.md`. The legacy `modal.confirm()`/`modal.load()`
    bridge to `#globalModal` is unaffected and stays the mechanism for legacy (non-Vue)
    flows. (The comment island's own delete dialogs no longer use it — see the
    REST-convergence entry below.)
  - Added `UserList` (`<user-list>`) to the user module's Vue component set
    (`protected/humhub/modules/user/vue/`, `UserVueAsset`) — a generic paginated user-list
    island (any endpoint returning `{ total, users, hasMore, nextPage }`), and extracted
    `humhub\modules\user\services\UserJsonService::serialize()` (the exact shape
    `CommentJsonService::serializeAuthor()` used to build; that method now delegates here,
    payload unchanged) so both share one implementation.
  - **Replaced**: the like module's user-list modal is now Vue-native. `LikeController::actionUserList()`
    (route `like/like/user-list`) now returns the JSON `{ total, users, hasMore, nextPage }`
    shape instead of rendering `humhub\modules\user\widgets\UserListBox` into the global
    modal — module-search found no external usage of this route (the `UserListBox` widget
    itself is unaffected and still used directly by several external modules, e.g.
    `humhub/mail`, `humhub/polls`, for their own unrelated user lists). `LikeButton.vue`'s
    like-count link now opens a `<UiModal>` containing `<UserList>` instead of delegating to
    `#globalModal`; `LikeVueAsset` gained `CoreVueAsset`/`UserVueAsset` dependencies for
    `<UiModal>`/`<UserList>`. Guest access is unchanged (`user-list` was never in
    `guestAllowedActions`, only `info` was). `LikeService::getUserQuery()`'s ordering gained a
    `like.id DESC` tiebreaker after `like.created_at DESC` — the datetime column's
    one-second resolution otherwise made offset/limit pagination over ties non-deterministic
    (a liker could appear twice, or be skipped, across two "Show more" pages).
  - **Comment module rendered as a Vue island** (`<comment-section>`,
    `humhub\modules\comment\assets\CommentVueAsset`) — comments render entirely client-side
    from JSON now, fed by a new `humhub\modules\comment\services\CommentJsonService` and JSON
    actions on `CommentController`: `list` (window/cursor pagination, replaces the HTML `show`
    action), `create`/`update` (replace `post`/`edit`), `info` (single comment, `showBlocked=1`
    reveal). The server now enforces at most one nesting level on `create` (previously
    JS-only). `humhub\modules\comment\widgets\Comments`'s public API is unchanged
    (`Comments::widget(['content' => $content])`, `parentComment`, `renderOptions`,
    `viewMode`) — the legacy `object` param (`Comments::widget(['object' => $x])`, the API
    before #7917 replaced polymorphic `object` relations with `content_id`/
    `parent_comment_id`) keeps working too, restored as a compatibility mapping onto
    `content`/`parentComment` since module-search found 10 external modules still using it
    (`CommentLink::widget(['object' => $x])` likewise - 2 known callers, also kept working).
  - **Removed**: `humhub\modules\comment\widgets\Comment`, `ShowMore`, `Form`, `EditLink`,
    `CommentControls` (+ their view files, `widgets/views/comments.php`,
    `views/comment/edit.php`) and the HTML branches of `CommentController::actionShow()`
    (only the `mode=popup` branch remains, now rendering the island via `Comments::widget()`)
    / `actionPost()` / `actionEdit()` / `actionLoad()`. `humhub.comment.js` shrinks from a
    full jQuery widget module to a ~90-line bridge: `toggleComment` (same
    `data-action-click="comment.toggleComment"` target in `comment/widgets/views/link.php`,
    now dispatching a `humhub:comment:toggle` CustomEvent on the island's mount element) and
    `scrollActive`/`scrollInactive` (still wired into the comment form's `RichTextField`, see
    `CommentFormShell` below) are all that remain callable from markup; a new
    `humhub:comment:countChanged` listener keeps the wall entry's "Comment (n)" badge
    (rendered by `CommentLink`, unchanged) in sync with the island. Every other export
    (`Comment`, `Form`, `showMore`) and their prototypes are gone. `CommentAsset` (the bridge)
    stays part of `CoreBundleAsset`; the new `CommentVueAsset` (depends on `LikeVueAsset` +
    `CoreApiAsset` - `LikeVueAsset` must register first so `<LikeButton>` resolves inside
    `CommentEntry.vue`) is registered on demand by the widget. `CommentLink`,
    `CommentEntryLinks` are unchanged/kept (`AdminDeleteModal` is removed later in this
    same cycle, see the REST-convergence entry below) -
    islandizing `CommentLink` itself is a documented follow-up, not part of this change.
  - **Breaking for known external modules**, found via module-search while preparing this
    change (recorded here since they were not caught by an earlier "zero external consumers"
    pass that had gated the removal - module owners should audit before upgrading to 1.20):
    `humhub/reportcontent` and the private `cuzy-app/reaction` module hook
    `CommentControls::EVENT_INIT` / `CommentEntryLinks::EVENT_BEFORE_RUN` respectively to
    inject a menu entry/reaction picker into each comment's controls row. Both classes are
    kept (so they still exist/autoload - no fatal error), but neither is instantiated anymore
    (comment entries render from JSON in `CommentEntry.vue`, there is no more per-comment PHP
    widget pass to hook into), so both integrations silently stop firing. The private
    `cuzy-app/external-websites` module's `FirstCommentForm` widget `extends Form` directly -
    this **does** fatal (class no longer exists) and needs a follow-up fix before that module
    can run against 1.20. Separately, the `cuzy-app/saas` theme's override of
    `comment/widgets/views/comments.php` (`require`s the core file) breaks since that view is
    removed - override the `Comments` widget or the new `CommentSection.vue` component
    instead.
  - New reusable **`humhub\widgets\VueFormShell`** mechanism for wrapping deep jQuery form
    widgets inside a Vue island without server-rendering per instance (see
    `docs/develop/ui-js-vuejs-interop.md`, "Form-shell pattern"): the widget owns a bare
    `ActiveForm` shell (`action => '#'`, CSRF disabled, `acknowledge => true`) and a `content`
    closure supplies whatever fields the caller needs, with every element id built from the
    literal token `__VUEFORM__` (via the `VueFormShell::id()` helper); the client
    (`LegacyFormWrapper.vue`) clones the shell per form instance (main form, an open reply form
    per comment, an edit form) by replacing the token with a unique id before mounting. The
    comment form's `humhub\modules\comment\widgets\CommentFormShell` is the reference
    composition (`RichTextField`/`UploadButton` markup) on top of this mechanism; its upload
    field now carries the generic `vueform-upload` class (the former comment-only
    `main_comment_upload` class was dropped - no known theme/module CSS targeted it) that
    `LegacyFormWrapper.vue`'s upload lookup queries.
  - `CommentJsonService`'s serialized comment shape (introduced earlier in this same
    Unreleased cycle, never part of a stable release) replaced the `messageOutput` HTML
    envelope string with raw markdown (`message`) plus an explicitly-typed
    `messageRenderOptions` object — see `RichText::outputMarkdownAndRenderOptions()` and
    `docs/develop/ui-js-vuejs-interop.md`, "RichTextOutput". `RichTextOutput.vue` now takes
    `message`/`render-options` props instead of a single `output` HTML-string prop.
  - **Breaking**: because that path never builds an HTML string to append anything to,
    `AbstractRichText::EVENT_AFTER_RUN` (inherited from `yii\base\Widget::EVENT_AFTER_RUN`) and
    `AbstractRichText::EVENT_AFTER_OUTPUT` never fire for a comment message serialized via
    `RichText::outputMarkdownAndRenderOptions()`/`CommentJsonService` (they still fire normally
    for every other `RichText::output()`/`RichText::widget()` call elsewhere in core, and
    `AbstractRichText::EVENT_BEFORE_OUTPUT` still fires on this path too, since
    `getMarkdown()`'s `onBeforeOutput()` extension pipeline still runs — only the two AFTER
    hooks, whose entire purpose is appending markup to an HTML string, are skipped). This is a
    deliberate, accepted consequence of rendering comments client-side from JSON, not a bug to
    be fixed by re-firing them. Known affected modules: `humhub/legal`'s consent wrapper
    (`onAfterRunRichText`, hooking `EVENT_AFTER_RUN`), `humhub/linkpreview`'s `Viewer` widget
    (hooking `EVENT_AFTER_OUTPUT`), `humhub/translator`'s translate button (hooking
    `EVENT_AFTER_RUN`), and comment-related forks in the private `cuzy-app` modules. A module
    that appended markup to richtext output this way must migrate to the Vue extension
    mechanism instead: `humhub\components\api\SerializeEvent` to contribute payload data per
    comment (surfaced as the serialized comment's `extensions` map), and
    `registerSlotComponent`/`ExtensionSlot` (or a plain registered Vue component reading that
    payload) to render UI from it — see `docs/develop/ui-js-vuejs-interop.md` and
    `docs/develop/ui-js-vuejs-components.md`.
  - **The comment/like islands consume the platform's HTTP API** (`/api/v2`, shipped by core —
    see the API framework entry below and `docs/develop/concept-api.md`) instead of
    core-internal JSON controllers. Consequences in core:
    - **Removed**: the JSON actions of `comment\controllers\CommentController` (`list`,
      `info`, `create`, `update`, `delete` — all introduced earlier in this same Unreleased
      cycle, never released). Only the HTML routes remain (`show` popup mode,
      `perma`).
    - **Removed**: `like\controllers\LikeController` entirely, including the pre-1.19 routes
      `like/like/like`, `like/like/unlike` and the newer `like/like/info`/`like/like/user-list` —
      use `POST /api/v2/like`, `DELETE /api/v2/like`, `GET /api/v2/like/state` and
      `GET /api/v2/like/users` instead. The legacy `like.toggleLike` client had
      already been removed earlier in this cycle (see above), so no core markup calls the
      removed routes anymore.
    - **Removed**: `comment\services\CommentJsonService`, `comment\components\SerializeCommentsEvent`
      and `user\services\UserJsonService` (all unreleased artifacts of this cycle) — serialization
      lives in the owning module's serializer (`comment\serializers\CommentSerializer`,
      `user\serializers\UserSerializer`, `like\serializers\LikeSerializer`,
      `file\serializers\FileSerializer`); the batch extension point is
      `humhub\components\api\SerializeEvent` (same `addData()` accumulator API, plus a `type`
      filter). Blocked-author masking moved fully client-side (the viewer's own block list
      ships via `CoreJsConfig` `user.blockedUserIds`, also readable at
      `GET /api/v2/account/blocked-users`).
    - Added `comment\services\CommentDeleteService` (delete + optional author notification —
      extracted from the controller, shared with the API endpoint) and a `Comment` model
      validation rule enforcing the one-nesting-level constraint on every write path.
    - **Removed**: `comment\widgets\AdminDeleteModal` (+ its view
      `widgets/views/adminDeleteModal.php`), the `comment\models\AdminDeleteCommentForm`
      model and the `comment/comment/get-admin-delete-modal` route. The comment delete
      dialogs — the plain confirm AND the moderator "delete with reason + notify the
      author" mode — are one native Vue modal now
      (`comment\vue\components\CommentDeleteModal.vue`, built on `UiModal` + the
      `HumHubForm` field components), feeding the REST delete endpoint's
      `notify`/`message` parameters. That also drops the view's inline `<script>` (CSP
      nonce) and the jQuery form-scraping off `#globalModalConfirm`, so the comment island
      no longer touches the legacy modal bridge at all. Module-search found no external
      users of any of the three removed symbols; theme overrides of
      `comment/widgets/views/adminDeleteModal.php` no longer apply — override the
      `CommentDeleteModal` Vue component instead. The unrelated
      `content\widgets\AdminDeleteModal` (stream entries) is untouched.
  - **File uploads in Vue forms are native** (`UploadField`,
    `protected/humhub/vue/UploadField.vue`, see
    `docs/develop/ui-js-vuejs-forms.md`), fed by the new endpoints
    `POST /api/v2/file` and `DELETE /api/v2/file/<id>`
    (`humhub\modules\file\controllers\api\FileController`). The legacy
    `file\widgets\Upload*` widgets and `humhub.file.js` are untouched and stay the
    mechanism for server-rendered forms. Details:
    - `comment\widgets\views\commentFormShell.php` no longer renders `UploadButton`,
      `FileHandlerButtonDropdown`, `UploadProgress` or `FilePreview` — the shell is the
      richtext editor plus the (empty) `.richtext-create-buttons` row the island
      teleports its trigger and submit button into. A theme overriding that view must
      drop the upload half accordingly.
    - **Removed** from `humhub\vue\LegacyFormWrapper`: `getFileGuids()`, the upload
      reset inside `clear()`, and the `.vueform-upload` convention class it queried;
      `RichTextField` lost its `getFileGuids()` proxy with it. A shell now carries only
      widgets that have no native counterpart.
    - `file\widgets\FileHandlerButtonDropdown` gained `$itemsOnly`, which renders just
      the handlers' `<li>` entries (for a client-side upload field that owns its own
      trigger and dropdown).
    - `file\serializers\FileSerializer::file()` gained `mimeIcon` (the same icon class
      `FileHelper::getFileInfos()` ships to the legacy widgets). Purely additive.
    - **Accepted break:** a `FileHandlerCollection::TYPE_CREATE` handler that
      programmatically pushes a file into the legacy upload *widget instance* does
      nothing inside a Vue form. Its entries are still rendered (their
      `data-action-click` handlers still fire), core's own upload-by-type entries are
      handled natively, and the documented replacement for "here is an already-uploaded
      file" is a DOM event on the field:
      `element.dispatchEvent(new CustomEvent('humhub:file:attach', {detail: {files: [fileShape]}}))`.
  - **The notification dropdown and the overview page are Vue islands**
    (`NotificationMenu`, `NotificationOverview` in
    `protected/humhub/modules/notification/vue/`, `NotificationVueAsset`), fed by the new
    endpoints `GET /api/v2/notification` and `POST /api/v2/notification/mark-as-seen`
    (`notification\controllers\api\NotificationController`, shapes in
    `notification\serializers\NotificationSerializer`). What a notification class
    contributes is unchanged — the payload carries its own `html()` sentence, and the
    entry markup around it is rendered client-side. Details:
    - **Removed**: the client-side `notification` JS module (`humhub.notification.js`,
      `notification.NotificationDropDown`, `notification.OverviewWidget`,
      `notification.markAsSeen`) and `notification\assets\NotificationAsset` (with its
      entry in `CoreBundleAsset::STATIC_DEPENDS`). Module-search found no external users
      of any of it.
    - **Removed**: `notification\controllers\ListController` (both
      `/notification/list` and `/notification/list/mark-as-seen`),
      `notification\widgets\OverviewWidget` (+ its view),
      `notification\widgets\NotificationFilterForm` (+ its view),
      `notification\models\Notification::loadMore()` and the
      `widgets/views/overview.php` dropdown view. Zero external references to any of
      them; the API endpoints above replace the two routes.
    - `notification\widgets\Overview` (the top-menu widget) now renders the island and
      keeps `#notification_widget.btn-group`; the ids inside it
      (`#icon-notifications`, `#badge-notifications`, `#mark-seen-link`,
      `#dropdown-notifications`, `#notification_overview_list`,
      `#notification_overview_markseen`) are unchanged, so theme CSS and the product tour
      still apply. Theme overrides of the removed views no longer do — override the Vue
      components instead.
    - **Kept as the compatibility surface**: `humhub:notification:updateCount` is still
      triggered on every count change, `humhub:modules:notification:UpdateTitleNotificationCount`
      is still listened to, and the document title still adds
      `humhub.modules.mail.notification.getNewMessageCount()`. `humhub/mail` and its forks
      need no change. New: `humhub:notification:setCount` pushes a count INTO the island —
      that is how `UpdateNotificationCount` (the pjax layout addon) keeps the badge current.
    - `Notification::findGrouped()` gained a `max(notification.id) as group_max_id`
      aggregate and orders by it as the final tiebreaker, so cursor paging cannot return
      overlapping pages (notifications created within the same second were previously
      ordered arbitrarily). Existing callers only see a deterministic order.
    - **Deliberate UI change**: the overview page pages with a "show more" button instead
      of numbered pages (`LinkPager`), for the same reason the endpoint pages by cursor.
    - The web-target renderer (`notification\renderer\WebRenderer`,
      `@notification/views/default.php`, `layouts/web.php`) is untouched and still
      renders a notification server-side for anyone calling it — the platform itself no
      longer does.
    - Added `space\serializers\SpaceSerializer` and `SpaceImage` (`<space-image>`,
      `SpaceVueAsset`) as the space module's first Vue component, used for the space badge
      of an entry. The Vue bridge gained `pageTitle()`, the platform's own per-page title
      state (`ui.view`), which the island needs to prefix the document title without
      accumulating its own count.
  - **The status bar is a Vue island** (`StatusBar`, `protected/humhub/vue/StatusBar.vue`),
    mounted by `humhub\widgets\StatusBar` as `<status-bar id="status-bar">`. The `ui.status`
    JS module keeps its **public API unchanged** — `success`/`info`/`warn`/`error` with the
    same signatures, plus the `humhub:modules:log:setStatus` listener — and forwards to the
    island through the bridge (`humhub.vue.js`'s `status()`/`setStatusHandler()`, which
    queues messages triggered before the island mounts). None of the 18 modules calling
    `require('ui.status')` needs a change. Details:
    - **Removed** from `humhub.ui.status.js`: the `StatusBar` class (and its `module.statusBar`
      instance), `module.template` and `module.initState` — all internals of the jQuery
      implementation, no external users found via module-search.
    - **Messages render as text, not HTML.** The jQuery bar injected them with `.html()`.
      Entities produced by `Html::encode()`/`htmlspecialchars(ENT_QUOTES)` are still decoded
      by the façade, so a caller that pre-escapes its message text stays correct; a caller
      that passes deliberate **markup** (`<strong>…</strong>`) now sees it as text.
    - `humhub\components\View::endBody()` ships the flash message as a JSON string literal
      instead of HTML-encoding it into a JS string. Its former `&quot;` strip — needed to
      keep that literal valid — silently deleted every double quote from a flash message.
    - `_user-feedback.scss` gained the slide transition (`.status-bar-body` hidden by
      default, shown via `.status-bar-visible`) that jQuery used to animate, plus
      `.status-bar-toggle` for the details-toggle cursor. A theme that replaces this block
      (`$prev-user-feedback: true`) keeps working — the bar element only exists while a
      message is shown — but its bar appears and disappears without the slide until it
      adopts those two rules.
  - **The space membership button is a Vue island** (`MembershipButton`,
    `protected/humhub/modules/space/vue/`, `SpaceVueAsset`), fed by the new endpoints
    `GET|POST|DELETE /api/v2/space/<id>/membership`
    (`space\controllers\api\MembershipController`, shape in
    `space\serializers\MembershipSerializer`). One `POST` covers joining, applying and
    accepting an invite, one `DELETE` covers leaving, withdrawing and declining — which
    one it is follows from the current state and the space's join policy, decided server
    side. Both answer the new state, and the island renders every state itself, including
    the request-membership dialog (a native `UiModal` + `HumHubForm`). Details:
    - `space\widgets\MembershipButton` keeps its class, `$space` and
      `EVENT_INIT`/`EVENT_CREATE`, and its presentation is now properties instead of a
      per-button option array: `$buttonClass`, `$pendingClass`, `$memberClass`,
      `$togglerClass`, `$groupClass`, `$showMemberState`, `$reloadOnJoin`. **`$options`,
      `setDefaultOptions()` and `getOptions()` are removed** rather than deprecated — the
      array carried titles, urls and `data-action-*` attributes for markup that no longer
      exists. The properties are nullable, so an `EVENT_INIT` handler supplies a default
      without overriding what the call site configured:

      ```php
      // before
      $membershipButton->setDefaultOptions([
          'requestMembership' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
          'becomeMember' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
          'acceptInvite' => ['attrs' => ['class' => 'btn btn-accent btn-sm'], 'togglerClass' => 'btn btn-accent btn-sm'],
          'cancelPendingMembership' => ['attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
      ]);

      // after
      $membershipButton->buttonClass ??= 'btn btn-accent btn-sm';
      $membershipButton->togglerClass ??= 'btn btn-accent btn-sm';
      $membershipButton->pendingClass ??= 'btn btn-sm btn-outline-accent';
      ```

      Known affected: `humhub/enterprise-theme` (`Events::onInitSpaceMembershipButton()`) —
      it sets classes only, so the above is the whole migration. `cuzy-app/category-group`
      hooks `EVENT_CREATE` and subclasses the widget for its own `run()`; it touches
      neither `$options` nor the removed methods and needs no change.
    - **Removed**: `MembershipButton::sanitizeRequestOptions()` and the
      `space/widgets/views/membershipButton.php` view; `space\controllers\MembershipController`
      lost `actionRequestMembershipForm()` (`/space/membership/request-membership-form`)
      with the `views/membership/requestMembership.php` and `requestMembershipSave.php`
      views, `RequestMembershipForm::$options`, and the `requestMembershipSend()` function
      of the `space` JS module. Zero external references to any of them (module-search).
      Theme overrides of those views no longer apply — override the Vue component instead.
    - `MembershipController::getActionResult()` (the web `request-membership`,
      `invite-accept` and `revoke-membership` actions, all kept) always redirects now; an
      AJAX request used to be answered with the re-rendered button. That answer is what
      made the presentation options travel through the client, which #8381 had to harden.
    - The sibling `FollowButton` (still a server-rendered widget) is toggled by the island
      itself, by `data-content-container-id` + `.followButton`/`.unfollowButton` — the
      server no longer sends `data-show-buttons`/`data-hide-buttons`.
    - **A module restricting who may join a space must guard the API controller too.**
      Membership transitions no longer go only through
      `space\controllers\MembershipController`; a guard hooked onto its
      `EVENT_BEFORE_ACTION` (as `cuzy-app/category-group` has) needs the same handler on
      `space\controllers\api\MembershipController`.
  - **The friendship button is a Vue island** (`FriendshipButton`,
    `protected/humhub/modules/friendship/vue/`, `FriendshipVueAsset`), fed by the new
    endpoints `GET|POST|DELETE /api/v2/user/<id>/friendship`
    (`friendship\controllers\api\FriendshipController`, shape in
    `friendship\serializers\FriendshipSerializer`) — the membership button's twin, built the
    same way: one `POST` sends a request or accepts a received one, one `DELETE` withdraws,
    denies or ends, both answer the new state. The endpoints answer `404` while the friendship
    system is disabled, like the web controller. Details:
    - `friendship\widgets\FriendshipButton` keeps its class, `$user`, `EVENT_INIT` and
      `isVisibleForUser()`, and gained the presentation properties `$buttonClass`,
      `$stateClass`, `$togglerClass`, `$groupClass`. **`$options`, `setDefaultOptions()`,
      `getOptions()` and `sanitizeRequestOptions()` are removed** (with the
      `widgets/views/friendshipButton.php` view and the request-options unit test) — the array
      carried titles, urls and `data-action-*` attributes for markup that no longer exists.
      Like the membership button the properties are nullable, so an `EVENT_INIT` handler can
      set a default with `??=` without overriding the call site. The option array allowed a
      separate class per button, the properties give the three state buttons (pending,
      received request, friends) one look — which is what both core call sites always did.
    - `RequestController::getActionResult()` (the web `request/add` and `request/delete`
      actions, both kept) always redirects now.
    - **Removed**: the `relationship` action of the `content.container` JS module
      (`data-action-click="content.container.relationship"`) together with its
      `data-button-options` posting and the `data-show-buttons`/`data-hide-buttons`
      convention. It existed for the membership and friendship buttons only, both of which
      are islands now; module-search found no external users of any of the three attributes.
      `content.container.follow`/`unfollow` are untouched.
  - **The activity box is a Vue island** (`ActivityBox`,
    `protected/humhub/modules/activity/vue/`, `ActivityVueAsset`), fed by the new endpoint
    `GET /api/v2/activity` (`activity\controllers\api\ActivityController`, shape in
    `activity\serializers\ActivitySerializer`). Unlike the buttons the island is the whole
    panel, not just its list: `activity\widgets\ActivityBox` renders the mount point with the
    first page inlined, the container it is scoped to and the rendered `PanelMenu` (whose
    entries modules keep contributing server-side). What an activity class contributes is
    unchanged — the payload carries its own `asWeb()` sentence, and the entry markup around it
    is rendered client-side. `#panel-activities`, `#activity-box-content.activities`,
    `div.activity-entry[data-activity-id]` and `.activity-box-entry` are unchanged, so theme
    CSS still applies. Details:
    - **Removed**: the client-side `activity` JS module (`humhub.activity.js`,
      `activity.ActivityBox`) and `activity\assets\ActivityAsset` (with its entry in
      `CoreBundleAsset::STATIC_DEPENDS`). The custom scrollbar it installed (`niceScroll`) is
      gone with it; the box scrolls natively, as its CSS always said.
    - **Removed**: `activity\controllers\ActivityBoxController` (the `/activity/activity-box/load`
      route), `activity\widgets\ActivityBox::renderActivity()`,
      `activity\services\RenderService::getWeb()` and the two view files
      `activity/widgets/views/activity-box.php` and `activity/views/layouts/web.php`. The
      controller's static `getQuery()` moved to `activity\services\ActivityWindowService::query()`,
      which the API and the widget share. `RenderService`'s mail representations are untouched.
      Module-search found no external users of any of it — a module rendering activities in mail
      or dispatching them through `ActivityManager` is unaffected.
    - New: `activity\live\NewActivity`, sent by `ActivityManager::dispatch()` after the
      grouping ran, for containers only (the live system routes by container and visibility).
      It carries no grouping detail: a consumer reacts by reading the list again.
  - **The space menu is a Vue island** (`SpaceChooser` and the small `SpaceChooserToggle`,
    `protected/humhub/modules/space/vue/`), fed by the new general space endpoints
    `GET /api/v2/space` and `GET /api/v2/space/states`
    (`space\controllers\api\SpaceController`, shapes in `space\serializers\SpaceSerializer`).
    The `<li>`, the button anchor and the ids theme CSS uses (`#space-menu`,
    `#space-menu-dropdown`, `#space-menu-spaces`, `#space-menu-search`) are unchanged, as is the
    markup of an entry (`[data-space-chooser-item]`, `data-space-guid`, the four relation
    attributes). What the menu shows is loaded when it is first opened, as before. Details:
    - **Removed**: the client-side `space.chooser` JS module (`humhub.space.chooser.js`, with the
      `niceScroll` scrollbar it installed) and `space\assets\SpaceChooserAsset` (with its entry
      in `CoreBundleAsset::STATIC_DEPENDS`). A module overriding functions of that JS module —
      `cuzy-app/classified-space` does — has to move to the island or ship its own.
    - **Removed** from `space\widgets\Chooser`: `$lazyLoad`, `$viewName`, `renderItems()`,
      `attachItem()`, `getLazyLoadResult()`, `getMemberships()`, `getFollowSpaces()`,
      `getViewParams()`, `getJsConfigParams()`, `configure()`, `canRun()` and
      `getNoSpaceHtml()`, together with the `widgets/views/spaceChooser.php` view. A theme
      subclassing the widget to render its own menu — `humhub/enterprise-theme` does — no longer
      has those hooks: the markup is the island's. Modules that need their own data on a space
      can attach it through the API's `SerializeEvent` (`extensions`).
    - **Removed**: `space\controllers\BrowseController::actionSearchLazy()` (the route
      `/space/browse/search-lazy`), which existed only to render the menu's list. `search-json`
      is untouched, including its `target=chooser` mode, and so are
      `space\widgets\SpaceChooserItem` and `Chooser::getSpaceResult()` — the space picker and
      modules (`humhub/sharebetween`, `cuzy-app/cloner`, `cuzy-app/group-advanced`) use them.
    - Following or unfollowing a space now triggers `humhub:space:followed` /
      `humhub:space:unfollowed` (`humhub.content.container.js`) instead of calling into the
      space menu's widget. Anything that kept its own copy of the menu list can listen for them.
  - **Attached files are a Vue island** (`AttachedFiles`, `protected/humhub/modules/file/vue/`,
    `FileVueAsset`). `file\widgets\ShowFiles` keeps its class, `$object`, `$active` and
    `$preview` and is now only the mount point: it serializes the record's stream files with
    `file\serializers\FileSerializer` and hands them over as props. The six modules calling
    `ShowFiles::widget(['object' => $record])` (`humhub/mail`, `cuzy-app/mail`,
    `cuzy-app/events-manager`, `cuzy-app/post-to-wiki`, `cuzy-app/survey`,
    `cuzy-app/survey-advanced`) need no change. `.hideOnEdit`, `.post-files`,
    `.post-files-audio|-videos|-images`, `.col-media`, `.well.post-file-list`, `ul.files` and
    `li.file-preview-item.mime.<mimeIcon>` are unchanged, so theme CSS still applies; the
    comment island renders its attachments with the same component now, which is what the
    change is really for — the two used to be separate implementations of the same visual.
    Details:
    - **Removed**: the view `file/widgets/views/showFiles.php`. A theme overriding it has to
      move to the component (or render its own island); module-search found no override.
    - **Removed**: `humhub\widgets\JPlayerPlaylistWidget` with its `jPlayerAudio.php` view,
      `humhub\assets\JplayerAsset`, `humhub\assets\JplayerModuleAsset`, the `media.Jplayer` JS
      module (with its entry in `CoreExtensionAsset`) and the `npm-asset/jplayer` +
      `xj/yii2-jplayer-widget` composer dependencies. `ShowFiles` was the only user of any of
      it (module-search: no external ones) — **attached audio now plays in native, individually
      labelled `<audio controls>` players instead of a jPlayer playlist**, and the `.jp-*`
      theme rules in `_file.scss`/`_comment.scss` are gone.
    - **Fixed**: the `excludeMediaFilesPreview` setting of the file module ("Exclude media
      files from stream attachment list", on by default for new installations) works again.
      The `file.Preview` JsWidget only added a `hiddenFile` class to those entries, and no rule
      for it has existed since the Bootstrap 5 migration, so media files stayed in the list
      below the preview grid. They are now really left out.
    - The `file.Preview` JsWidget itself is untouched and still renders the file list of the
      upload/edit paths (`wallCreateContentFormFooter.php`, `post/edit.php`,
      `file\widgets\Upload`, `file\widgets\ActiveFileUpload`), where it doubles as the live
      preview of an in-progress upload.
    - Smaller deviations: attachment sizes are formatted client-side rather than through
      `Yii::$app->formatter->asShortSize()`, and the preview grid no longer carries a
      `post-files-<uniqueId>` id (nothing referenced it).
    - New in the API's file shape: **`downloadUrl`**, the same file with the response forcing a
      download. Purely additive. The two hints `ShowFiles` resolves per file — `viewUrl` (the
      file has a viewer beyond plain download) and `highlight` (a search hit) — are deliberately
      **not** part of it: both are caller specific, and the comment payload is cached
      caller-neutrally (see `docs/develop/concept-api.md`).
    - New helpers: `file\libs\FileHelper::getViewUrl()` (extracted from `createLink()`, whose
      output is unchanged) and `file\libs\FileHelper::isSearchHighlighted()` (moved from the
      protected `FilePreview::isHighlighed()`, which is removed).
- Added the **HTTP API framework** in `humhub\components\api\` and the first core endpoints
  under `/api/v2` — see `docs/develop/concept-api.md`. Purely additive for existing modules;
  the `humhub/rest` module and the 13 modules extending its `BaseController` keep working
  unchanged.
  - `humhub\components\api\BaseController` is the base class of a core API controller
    (`humhub\modules\<module>\controllers\api\`), `ApiRules::v2()` prefixes the rules a module
    declares in its `config.php` `urlManagerRules`, `Format` holds the v2 value conventions
    (ISO-8601 UTC timestamps, camelCase attribute names) and `SerializeEvent` is the batch
    serializer extension point. Serializers live in `humhub\modules\<module>\serializers\`.
  - Core endpoints in this release: comment window/CRUD (`comment`), like state/toggle/users
    (`like`), the caller's account and block list (`user`), file upload/delete (`file`),
    the notification window (`notification`), the caller's space membership (`space`) and the
    caller's friendship with a user (`friendship`).
  - A module may contribute authentication methods to *every* API controller by handling
    `BaseController::EVENT_COLLECT_AUTH_METHODS` (`AuthMethodsEvent`) — how the rest module
    ≥ 0.13 makes token authentication apply to core endpoints. Browser-session authentication
    ships in core (`humhub\components\api\SessionAuth`) and is opt-in **per controller**
    (`BaseController::$allowSessionAuth`, default `false`), so a module's own token-oriented
    endpoints never become reachable from a browser session.
  - Added `humhub\modules\user\components\User::$sessionAuthenticated` (set by `SessionAuth`)
    and `User::isSessionBased()`. `humhub\components\gates\GateFilter` no longer infers
    `RequestClass::Api` from `Yii::$app->user->enableSession` alone, and
    `humhub\modules\user\components\Impersonation::isActive()` no longer short-circuits on
    it: a session-authenticated API request passes the normal user gates (2FA, legal,
    onboarding) and keeps the impersonation restrictions. A module that implements its own
    API-style authentication should set the flag when it authenticates someone by their
    browser session; a module that needs to tell browser from machine clients should call
    `isSessionBased()` instead of reading `enableSession`.
  - **The API payloads are caller-neutral**: nothing in a comment or user shape depends on who
    is asking, so one serialization serves every reader (and can be cached). Consequences for
    a module reading these shapes or attaching to them:
    - `comment\serializers\CommentSerializer` no longer emits `canEdit`, `canDelete` or
      `likes`; `user\serializers\UserSerializer::short()` no longer emits `online` (nor the
      localized `imageAlt`, which `user/vue/UserImage.vue` builds itself). The replacements are
      `GET /api/v2/comment/<id>/permissions` (fetched when an entry's context menu opens) and
      `GET /api/v2/like/states?recordIds=…` (one batched request per window, `{recordId:
      {total, liked, canLike}}`).
    - Data attached via `humhub\components\api\SerializeEvent` must be **caller-neutral**
      too — caller-specific module state belongs in that module's own endpoint, see
      `docs/develop/ui-js-vuejs-extensions.md`.
    - Added `like\serializers\LikeSerializer::statesForRecords()`/`statesByRecordId()`,
      `like\services\LikeService::countsForRecords()`/`likedRecordIds()`/`preloadState()`
      and `humhub\models\RecordMap::getByIds()` — batched building blocks for the above,
      reusable wherever many records are serialized at once (stream entries next).
    - `humhub\vue\DropdownMenu` gained an `open` event (fired from Bootstrap's
      `show.bs.dropdown`) and a `loading` prop, so a menu can load its content on demand.
    - Because the payloads are caller-neutral they are now **cached server-side**:
      `comment\services\CommentPayloadCache` wraps the serializer and is what the comment
      widget and the API read. A comment create/edit/delete retires its content's entries
      immediately (`Comment::afterSave()`/`afterDelete()`); the new `comment` module property
      `payloadCacheTtl` (default `3600`, `0` disables) only bounds how long a payload may lag
      behind data it embeds without owning — the author's display name and profile image, and
      whatever a module attached through `SerializeEvent`. A module whose `SerializeEvent`
      data can change independently of the comment should keep that in mind (or attach it
      client-side instead).
- **Breaking**: `humhub\modules\content\widgets\richtext\extensions\RichTextExtension` gained a
  new interface method, `getRenderOptions(): array`, needed for
  `ProsemirrorRichText::getMarkdownAndRenderOptions()` (the client-render counterpart of
  `RichText::output()` backing the comment payload change above) to let an extension
  contribute per-record data — e.g. `OembedExtension`'s server-fetched preview HTML — that a
  client cannot derive from the processed markdown text alone. A custom `RichTextExtension`
  implementation registered via `AbstractRichText::addExtension()` must add this method
  (return `[]` if it has nothing to contribute — true for the overwhelming majority of
  extensions, whose entire contribution already lives in the markdown text their
  `onBeforeOutput()` returns). Module-search found zero external implementers of this
  interface as of this writing; extending `RichTextContentExtension`/`RichTextLinkExtension`
  (the base classes every core extension uses) already provides the new method as a no-op
  default, so most custom extensions need no change at all.

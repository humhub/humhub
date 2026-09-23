# Module Migration — Version 1.20

Breaking changes, new APIs and deprecations of the 1.20 release cycle.

- **Font Awesome 4 replaced by Tabler Icons.** `humhub\widgets\Icon` renders
  `<i class="ti ti-<name>">` through the new `humhub\components\icon\TablerIconProvider`, and Tabler
  names (https://tabler.io/icons) are the canonical icon names from now on — `Icon::get('pencil')`,
  `Icon::get('settings')`, `Icon::get('star-filled')` for a filled variant. `Icon::getNames()` returns
  the names of the installed package. Font Awesome is no longer shipped.

  - **Keeps working unchanged in 1.20 and 1.21:**
    - Raw `<i class="fa fa-<name>">` markup in PHP, JavaScript and views — the compatibility
      stylesheet `css/icon-legacy.css` (loaded by `humhub\assets\IconAsset`, part of `AppAsset`)
      renders every Font Awesome 4 class with the matching Tabler glyph. The size and utility
      classes `fa-fw`, `fa-xs` … `fa-10x`, `fa-spin`, `fa-pull-left/right` are covered as well;
      `fa-stack`, `fa-ul`, `fa-li`, `fa-rotate-*`, `fa-flip-*` and `fa-border` are not.
    - Font Awesome 4 names passed to `Icon::get()`, `->icon()` of the Bootstrap widgets, `'icon' =>`
      of menu entries, `Module::ICON`, `getIcon()` of content types and stored in the database —
      `humhub\components\icon\LegacyIconMap` translates them, `fa-` prefixes are stripped.
    - `FontAwesomeAsset::register()` and `Icon::get('x', ['lib' => 'fa'])`.
  - **Breaks immediately:**
    - Theme SCSS and custom SCSS styling `.fa` on icons rendered by the core — they carry `ti` now,
      style `.ti` instead. Raw markup of your own keeps its `fa` class.
    - Acceptance tests and module JavaScript that select or toggle classes such as `.fa-bell` on
      core-rendered markup, e.g. `AcceptanceTester::seeInNotifications()` now clicks `.ti-bell`.
    - `Icon::getNames()` and the `IconPicker` return and store Tabler names; a validator comparing
      a picker value against a Font Awesome list rejects every new value. A stored Font Awesome
      name is preselected as its Tabler counterpart and saved as such on the next submit.
    - A name both libraries know renders the Tabler icon, and Tabler draws it as outline where Font
      Awesome drew it solid: `star`, `user`, `trash`, `bell`, `calendar`, `caret-*`, `file`,
      `file-text`, `folder`, `folder-open`, `flag`, `heart`, `bookmark`, `circle`, `square`, `send`,
      `star-half`. Write `star-filled` where the solid look matters. A few shared names mean
      something else in Tabler: `share` is the share-nodes symbol (Font Awesome's forward arrow is
      `arrow-forward-up`), `cut` a cutting line (`scissors`), `exchange` a currency exchange
      (`arrows-exchange`), `repeat` a loop (`rotate-clockwise`), `globe` a globe on a stand (`world`),
      `glass` a wine glass, `mars`/`venus` planets (`gender-male`/`gender-female`), `wheelchair` a
      wheelchair (`disabled`), `apple` a fruit (`brand-apple`), `steam` steam (`brand-steam`),
      `id-badge` a badge (`id-badge-2`), `exclamation-circle` an inverted alert (`alert-circle`),
      `reorder` drag handles (`menu-2`), `mail-forward` a mail (`arrow-forward-up`). Raw
      `fa fa-star` markup is unambiguous and keeps the solid glyph.
    - Brand and vendor icons without a Tabler counterpart render nothing: `adn angellist buysellads
      connectdevelop dashcube delicious digg empire forumbee ge gittip gratipay ioxhost joomla jsfiddle
      leanpub linux maxcdn meanpath openid pagelines pied-piper pied-piper-alt qq ra rebel renren
      sellsy shirtsinbulk simplybuilt skyatlas slideshare stack-exchange stumbleupon
      stumbleupon-circle tencent-weibo viacoin vine yelp`.
  - **Deprecated, removed in 1.21:** `humhub\assets\FontAwesomeAsset` (an empty shim depending on
    `IconAsset`), `humhub\components\icon\FontAwesomeIconProvider` (still registered as `fa`),
    `Icon::$names` (the Font Awesome list), `Icon::$listItem`, `Icon::$border`, `Icon::listItem()`,
    `Icon::border()`, `Icon::renderList()`, `IconFactory::renderList()` and
    `IconProvider::renderList()` — none of them rendered by the Tabler provider, none used by any
    known module.
  - **Removed in 1.22:** `css/icon-legacy.css` and `LegacyIconMap`. They stay one release line
    longer than the shims because they carry third-party modules and data stored in databases.
  - The most common renames — the complete table is `LegacyIconMap::MAP`:

    | Font Awesome 4 | Tabler | | Font Awesome 4 | Tabler |
    |---|---|---|---|---|
    | `cog`, `cogs`, `gear` | `settings` | | `times`, `close`, `remove` | `x` |
    | `pencil` | `pencil` | | `trash-o` | `trash` |
    | `edit`, `pencil-square-o` | `edit` | | `check-circle-o` | `circle-check` |
    | `times-circle-o` | `circle-x` | | `plus-circle` | `circle-plus` |
    | `minus-circle` | `circle-minus` | | `info-circle` | `info-circle` |
    | `exclamation-triangle` | `alert-triangle` | | `exclamation-circle` | `alert-circle` |
    | `question-circle-o` | `help-circle` | | `globe` | `world` |
    | `envelope-o` | `mail` | | `comment-o` | `message-circle` |
    | `comments-o` | `messages` | | `bell-o` | `bell` |
    | `bell-slash-o` | `bell-off` | | `eye-slash` | `eye-off` |
    | `unlock`, `unlock-alt` | `lock-open` | | `user-o` | `user` |
    | `group` | `users` | | `clock-o` | `clock` |
    | `calendar-o` | `calendar` | | `tachometer`, `dashboard` | `dashboard` |
    | `bars`, `navicon`, `reorder` | `menu-2` | | `ellipsis-h` / `ellipsis-v` | `dots` / `dots-vertical` |
    | `angle-down` / `-up` / `-left` / `-right` | `chevron-down` / `-up` / `-left` / `-right` | | `angle-double-*` | `chevrons-*` |
    | `caret-down` (solid) | `caret-down-filled` | | `arrow-circle-right` | `circle-arrow-right-filled` |
    | `arrows-h` / `arrows-v` | `arrows-horizontal` / `arrows-vertical` | | `arrows-alt`, `expand` | `arrows-maximize` |
    | `paper-plane`, `send` | `send-filled` | | `paper-plane-o`, `send-o` | `send` |
    | `mail-reply`, `reply`, `undo` | `arrow-back-up` | | `mail-forward`, `share` | `arrow-forward-up` |
    | `share-alt` | `share` | | `external-link` | `external-link` |
    | `floppy-o`, `save` | `device-floppy` | | `print` | `printer` |
    | `file-o` | `file` | | `file-text-o` | `file-text` |
    | `file-pdf-o` | `file-type-pdf` | | `file-word-o` | `file-type-doc` |
    | `file-excel-o` | `file-spreadsheet` | | `file-image-o`, `picture-o`, `image`, `photo` | `photo` |
    | `file-archive-o`, `file-zip-o` | `file-zip` | | `folder-o` / `folder-open-o` | `folder` / `folder-open` |
    | `cloud-upload` / `cloud-download` | `cloud-upload` / `cloud-download` | | `upload` / `download` | `upload` / `download` |
    | `search` | `search` | | `search-plus` / `search-minus` | `zoom-in` / `zoom-out` |
    | `filter` | `filter` | | `sliders` | `adjustments` |
    | `sign-in` / `sign-out` | `login` / `logout` | | `user-plus` / `user-times` | `user-plus` / `user-x` |
    | `map-marker` | `map-pin-filled` | | `thumb-tack` | `pin` |
    | `mobile`, `mobile-phone` | `device-mobile` | | `desktop` / `laptop` / `tablet` | `device-desktop` / `device-laptop` / `device-tablet` |
    | `bar-chart`, `bar-chart-o` | `chart-bar` | | `line-chart` / `pie-chart` / `area-chart` | `chart-line` / `chart-pie` / `chart-area` |
    | `thumbs-up` / `thumbs-o-up` | `thumb-up-filled` / `thumb-up` | | `star` / `star-o` | `star-filled` / `star` |
    | `heart` / `heart-o` | `heart-filled` / `heart` | | `circle-o`, `circle-thin` | `circle` |
    | `dot-circle-o` | `circle-dot` | | `check-square-o` / `square-o` | `square-check` / `square` |
    | `lightbulb-o` | `bulb` | | `wrench` | `tool` |
    | `magic` | `wand` | | `flash`, `bolt` | `bolt` |
    | `github`, `facebook`, `twitter`, `linkedin`, `google`, … | `brand-github`, `brand-facebook`, … | | `usd`, `eur`, `gbp`, … | `currency-dollar`, `currency-euro`, `currency-pound`, … |

  - **Migrating a module** — this is what `/humhub:refactor-modules` does per repository:
    1. Replace raw `<i class="fa fa-<name>">` in PHP with `Icon::get('<tabler name>')`, in
       JavaScript templates with `ti ti-<tabler name>`, and class toggles such as
       `toggleClass('fa-caret-down')` with the Tabler class — `ti-caret-down-filled` here, so the
       look stays.
    2. Replace Font Awesome names in every icon hand-over (`Icon::get()`, `->icon()`, `'icon' =>`,
       `Module::ICON`, `getIcon()`, `$defaultIcon` properties) with Tabler names from the table.
    3. Change `.fa` selectors in the module's SCSS, LESS and CSS to `.ti`, `.fa-<name>` to
       `.ti-<tabler name>`, and `font-family: FontAwesome` glyphs to `tabler-icons` codepoints.
    4. Adjust test selectors.
    5. Replace an own icon list (custom-pages' `PageIconSelect`, devtools' `iconSelect.php`) with
       `Icon::getNames()` or the `IconPicker`.
    6. Tabler names do not exist on a 1.19 core, so the switch is a breaking change for the module:
       `develop` branch, next minor version, `humhub.minVersion` `1.20`, CHANGELOG entry referencing
       the core change. A module that wants to keep supporting 1.19 stays on Font Awesome names —
       they render through the map until 1.22.

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

- **The local configuration directory moved to `<installation root>/config`**, beside the `.env`
  file. `@config` resolves there instead of to `protected/config`, so anything addressed through
  the alias - translation overrides under `@config/messages`, view overrides under `@config/views`,
  a path a module builds from it - follows on its own. A module that spelled out `@app/config` or
  `protected/config` does not, and has to be changed.

  `protected/config/{common,web,console,dynamic}.php` are still loaded when they exist, with the
  new directory merged on top, so installations keep working across the update. None of the local
  configuration files is shipped any more - all four are optional, and the new directory holds only
  `common.example.php`, `web.example.php` and `console.example.php`.

  `params.dynamicConfigFile` names whichever `dynamic.php` is in effect and is an absolute path
  now rather than an alias; `Yii::getAlias()` on it stays correct. A migration moves an existing
  file into the new directory, and until it has been moved the old path stays in effect so that
  nothing writes a second one.

  The new `humhub\services\ConfigDirectoryService` answers where both directories are, what is left
  in the old one and which dynamic configuration is in use. Administration -> Information ->
  Prerequisites reports leftovers.

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
    configured `modules.ui.iconAlias` has to move the values. Since the `ui` module itself was
    removed further down this page, the old section is not rejected either - nothing reads the
    configuration of a module id that no longer exists, so it is ignored silently and the defaults
    apply again.

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
  The dependency moved to `humhub\modules\content\assets\StreamAsset`, where it belongs:
  `humhub.ui.filter.js` requires only `ui.widget` and `util` and has no relationship to topics,
  while it is `WallStreamFilterNavigation` — in the content module — that configures a
  `PickerFilterInput` with `TopicPicker`.

  Nothing changes about the scripts a page loads: `TopicAsset` is registered by the core bundle
  directly, and the transitive dependency closure of `CoreBundleAsset` is identical before and
  after. Only a module that registers `FilterAsset` on its own *and* renders a topic picker has
  to register `TopicAsset` itself now.

- **An event registered on a deprecated shim class is now redirected to its replacement.**
  `Event::trigger()` walks the parents of the sender upwards. A deprecated shim is a *subclass*
  of the class that replaced it, so a handler registered on the shim was never reached once the
  core instantiated the new class — the registration failed silently: no error, no log entry, the
  handler simply stopped running. This affected every `config.php` that named a class moved out of
  the `ui` module in this cycle, and would have affected the stream classes below.

  `ModuleManager` now maps such a class onto its parent before calling `Event::on()` and logs a
  warning naming the module, so those registrations keep working while the shims exist. Only the
  namespaces of dissolved modules are inspected, so no event class of an unrelated module is
  autoloaded for it.

  The redirect disappears with the shims in 1.21. Migrate the event configuration during the 1.20
  cycle; the warning in the application log tells you which registrations are affected.

- **The `stream` module is gone.** It had no controllers and no routes, and everything in it was
  content-specific — the queries build on `ActiveQueryContent`, the filters filter content, and
  the stream entry widgets already lived in the content module. The dependency even ran backwards:
  `ActiveQueryContent` and `ContentSearchService` read `showDeactivatedUserContent` off the stream
  module. It is dissolved into `content`.

  | Before | After |
  |---|---|
  | `humhub\modules\stream\actions\*` | `humhub\modules\content\actions\*` |
  | `humhub\modules\stream\models\*StreamQuery` | `humhub\modules\content\models\stream\*` |
  | `humhub\modules\stream\models\filters\*` | `humhub\modules\content\models\stream\filters\*` |
  | `humhub\modules\stream\widgets\StreamViewer` | `humhub\modules\content\widgets\stream\StreamViewer` |
  | `humhub\modules\stream\widgets\WallStreamFilterNavigation` | `humhub\modules\content\widgets\stream\WallStreamFilterNavigation` |
  | `humhub\modules\stream\helpers\StreamHelper` | `humhub\modules\content\helpers\StreamHelper` |
  | `humhub\modules\stream\events\StreamResponseEvent` | `humhub\modules\content\events\StreamResponseEvent` |
  | `humhub\modules\stream\assets\StreamAsset` | `humhub\modules\content\assets\StreamAsset` |
  | `humhub\modules\stream\Module` (module id `stream`) | - |
  | i18n category `StreamModule.base` | `ContentModule.base` |
  | i18n category `StreamModule.filter` | `ContentModule.filter` |
  | alias `@stream` | `@content` |

  - **Every old class name stays available as a deprecated subclass. The shims are removed in
    1.21** — migrate during the 1.20 cycle.

  - **`Yii::$app->getModule('stream')` returns `null` and cannot be shimmed.** The four module
    properties moved to the content module under the same names:

    ```php
    // before
    Yii::$app->getModule('stream')->streamSuppressQueryIgnore[] = News::class;

    // after
    Yii::$app->getModule('content')->streamSuppressQueryIgnore[] = News::class;
    ```

    `streamExcludes`, `streamSuppressQueryIgnore`, `streamSuppressLimit` and
    `showDeactivatedUserContent` are all configured on `content` now. An installation that sets
    them in `config/common.php` has to move them from the `stream` key to the `content`
    key. A module cannot work around this with a proxy module: `$module->streamSuppressQueryIgnore[]`
    is an indirect modification, which is lost through `__get()`.

  - **The database setting moved and was renamed.** `stream.defaultSort` becomes
    `content.defaultStreamSort` — the name the space module already uses for the same thing. A
    migration in the content module carries the existing value over; per-space and space-module
    defaults were never stored under `stream` and are untouched.

  - **`@stream` no longer resolves.** A widget pointing `$view` at
    `@stream/widgets/views/wallStreamFilterNavigation` has to use
    `@content/widgets/stream/views/wallStreamFilterNavigation`. This one throws rather than failing
    silently.

  - **Theme view overrides have to be moved, and this is the one change here that fails silently.**
    Themed views are resolved by file path, so a shim cannot cover them: an override left at the old
    path is simply never applied again — no error, no log entry, the core view is rendered instead.
    Both views of the two moved widgets are affected:

    | Before | After |
    |---|---|
    | `themes/<theme>/views/stream/widgets/views/wallStream.php` | `themes/<theme>/views/content/widgets/stream/views/wallStream.php` |
    | `themes/<theme>/views/stream/widgets/views/wallStreamFilterNavigation.php` | `themes/<theme>/views/content/widgets/stream/views/wallStreamFilterNavigation.php` |

    The stream entry views were already in the content module and do not move.

  - **The deprecated `StreamAsset` is a subclass, so Yii registers it under its own name.** A page
    that registers both the old and the new bundle emits the stream scripts twice. Register only
    one of them.

  - The JavaScript module ids (`stream`, `stream.Stream`, `stream.wall`, …) are unchanged; only
    the files moved, to `@content/resources/js`.

  - The two migrations moved into the content module, where they always belonged — both of them
    alter the `content` table. The migration history records the class name, not the path, so
    nothing runs again on an existing installation.

  - The module's test suite moved into the content suite, so `grunt test --module=stream` no
    longer exists. `StreamQueryTest` joins the content unit tests, `StreamCest` and `TopicCest`
    the content acceptance tests.

- **The theming component stores its state in two settings instead of ~152.**

  | Before | After |
  |---|---|
  | `theme` — absolute base path | `theme` — theme name, e.g. `HumHub` |
  | `themeParents` — JSON array of absolute paths | gone, part of `theme.state` |
  | `theme.var.<Theme>.<key>` — one row per variable | gone, part of `theme.state` |

  A module reading the `theme` setting directly now gets a name; resolve it with
  `ThemeHelper::getThemeByName()`. The new `humhub\services\ActiveThemeService` is the single
  place that resolves the active theme — it keeps the resolved path, the parent chain and the
  SCSS variables in the `theme.state` setting and validates them against the theme name, the
  system revision, the custom SCSS and the modification time of every `scss/variables.scss` in
  the theme tree. Call `ActiveThemeService::flush()` if your module replaces a theme's
  `scss/variables.scss` without advancing its modification time — for example by unpacking an
  archive with stored timestamps.

  An installation that pins `theme` through fixed settings
  (`HUMHUB_FIXED_SETTINGS__BASE__THEME`, or `params['fixed-settings']['base']['theme']`) to an
  absolute path is not migrated, because `SettingsManager::set()` silently does nothing on a
  fixed setting — such a value has to be changed to the theme name by hand.

  Removed: `ThemeVariables::SETTING_PREFIX`, `ThemeVariables::$module`, and the protected
  `ThemeVariables::ensureLoaded()`, `storeVariables()`, `getSettingKey()`, `getSettingPrefix()`,
  together with `Theme::getActiveParents()`. `Theme::variable()`, `ThemeVariables::get()`,
  `ThemeVariables::getCustom()` and `ThemeVariables::flushCache()` keep their signatures;
  `flushCache()` now drops the active theme's whole state regardless of which theme the instance
  wraps.

  `ThemeHelper::getAllVariables()` takes a second parameter `bool $includeCustomScss = true`.

  Two behavioural details that fail silently:

  - `Theme::getParents()` returns an array **keyed by theme name** for the active theme as well.
    It previously returned a numeric list in that one case and a name-keyed array otherwise; both
    paths are consistent now. Code indexing the result numerically has to be adjusted.
  - Theme variables were previously read back through the settings manager, which turned numeric
    strings into integers. They now come back as the string the SCSS file contains. A comparison
    with `===` against an integer has to be adjusted; `==` and casts are unaffected.

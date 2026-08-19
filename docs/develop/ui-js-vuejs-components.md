# Vue.js Components

> Part of the [Vue.js integration](ui-js-vuejs.md) documentation. This chapter covers authoring and using components: where a module's Vue sources live, the components core ships, the registry, mounting islands into server-rendered pages, the `VueComponent` PHP widget, and the composables bridging into existing platform services. For motivation, goals, constraints and the overall architecture, see the [overview](ui-js-vuejs.md).

## Module file layout

```
protected/humhub/modules/like/
├── assets/
│   └── LikeVueAsset.php              # AssetBundle → js/humhub.like.vue.js
├── vue/                              # sources — plain source code like views/, never published
│   └── LikeButton.vue                # auto-registered under its filename
└── resources/
    └── js/
        ├── humhub.like.vue.js        # committed build artifact
        └── humhub.like.vue.js.map
```

Every top-level `.vue` file directly inside `vue/` is registered under its filename — `LikeButton.vue` becomes `register('LikeButton', ...)`, no code required. The filename is therefore a component name in the same sense as a registered name below: PascalCase with a dashed tag form, enforced at build time (a violating filename fails the build with a message naming the offending file). Files inside subdirectories of `vue/` are internal building blocks — imported by the public, top-level components but never registered themselves.

An optional `vue/index.js` replaces the generated entry when a module needs custom registration logic; when present it is used verbatim and the filename convention above does not apply. That is where explicit `register()` calls against `@humhub/vue` go:

```js
import { register } from '@humhub/vue';
import LikeButton from './LikeButton.vue';

register('LikeButton', LikeButton);
```

The `@humhub/vue` import is developer-experience sugar only: the build marks it external and maps it onto the `humhub.modules.vue` global — nothing gets bundled twice.

External modules use the identical layout relative to their module root.

## Core component set

Core ships its own components at `protected/humhub/vue/`, built via `grunt build-vue --module core` into `protected/humhub/resources/js/humhub.core.vue.js` (`CoreVueAsset`) — infrastructure shared platform-wide, not tied to any single module, and always available wherever any other module's island might nest them (`CoreVueAsset` is listed in `CoreBundleAsset::STATIC_DEPENDS`).

- **`RichTextOutput`** — renders a server-generated RichText envelope (`RichText::output()`) inside an island via `v-html` + `v-additions`. See [Legacy interop: RichTextOutput](ui-js-vuejs-interop.md#richtextoutput) for the trusted-source contract.
- **`LegacyFormWrapper`** — hosts a server-rendered legacy widget shell (rich text editor, upload widget, ...) inside an island and exposes a small, typed API to the rest of the Vue tree. See [Legacy interop: LegacyFormWrapper](ui-js-vuejs-interop.md#legacyformwrapper) for the `__VUEFORM__` token contract and the widget-instance APIs it exposes.
- **`DropdownMenu`** — a generic dropdown-toggle menu, the Vue analog of the `nav nav-pills preferences` / `.dropdown-toggle` + `.dropdown-menu` markup pattern PHP widgets render throughout the app (e.g. `humhub\widgets\PanelMenu`, `content\widgets\WallEntryControls`). Any island's template can reach for `<DropdownMenu>` instead of hand-rolling this structure again; its default slot holds the menu items. Toggling, closing (click-away/Escape) and keyboard navigation are handled entirely by Bootstrap's own dropdown JS via `data-bs-toggle="dropdown"` — nothing here is Vue-owned, so Vue-rendered markup behaves identically to server-rendered markup.
- **`ExtensionSlot`** — the Vue analog of PHP widget stacks. See [Extending islands: extension slots](ui-js-vuejs-extensions.md#extension-slots) for the full contract.

**Base component library.** A gradually growing set of platform components in HumHub markup and theme styling: `HButton`, `HDropdown`, form components (`HForm`, inputs with server-side validation error display), `HTimeAgo`, `HUserImage`, … Phase 1 contains only what the pilot components need; the library grows with real usage instead of being designed up front.

## Loading and bundling

- **Vue runtime:** Vue 3, runtime-only build, added like all frontend libraries via Composer/asset-packagist (`npm-asset/vue`). A `VueAsset` bundle serves `vue.runtime.global.js` in debug mode (warnings, Vue Devtools support) and `vue.runtime.global.prod.js` otherwise. It becomes part of the core bundle, so every module asset bundle (which implicitly depends on `CoreBundleAsset`) is guaranteed to load after it.
- **`humhub.vue` core module:** a new `protected/humhub/resources/js/humhub/humhub.vue.js`, part of the core JS list like `humhub.i18n.js`. Exposes the registry, the mounter and the composables under `humhub.modules.vue`.
- **Module components:** each module ships one committed artifact (`resources/js/humhub.<module>.vue.js`) registered through a normal `AssetBundle`. Yii's per-page asset registration is the lazy-loading mechanism — a module's Vue code only loads on pages that render one of its components. Nothing loads globally except the runtime and `humhub.vue`.
- **Later stage (optional):** on-demand loading via dynamic `import()` at first mount for heavy components. The registry API already anticipates this (a component may be registered as an async loader), but the first iteration relies on asset bundles only.

## Component registry

At the core, every component is registered by name through a small API:

```js
import { register } from '@humhub/vue';
import LikeButton from './LikeButton.vue';

register('LikeButton', LikeButton);
```

Modules normally never write this code themselves — the filename convention (see [Module file layout](#module-file-layout)) generates it for every top-level component. The explicit `register()` call remains the underlying API for two cases: a `vue/index.js` with custom registration logic, and registering components at runtime (e.g. `registerSlotComponent`, see [Extending islands: extension slots](ui-js-vuejs-extensions.md#extension-slots)).

- **Names are platform-wide unique**, PascalCase with a dashed tag form (`LikeButton` → `<like-button>`, `HButton` → `<h-button>`, `PDFViewer` → `<pdf-viewer>`) — analogous to PHP class names sharing one autoloader. Registering the same name twice is a debug-level no-op (artifact scripts legitimately re-execute with every ajax response that includes them); two different names deriving the same tag is an error, and the first registration wins.
- Every registered component is made available **globally in every island app**. That is what enables cross-module nesting: the comment section's template uses `<LikeButton :content-id="..."/>` without importing or even knowing the like module.
- A tag for an **unregistered** component (module disabled, artifact not loaded) renders as an inert placeholder instead of an error — modules stay optional. In debug mode a console warning identifies the missing component.
- **Late registration is safe:** when a component registers after the page initialized (script injected with a PJAX/AJAX response), the registry immediately mounts any placeholder tags already waiting in the DOM. "HTML first, script afterwards" ordering is therefore uncritical. Late-registered components also become *resolvable* inside already-mounted islands — but an island that has already rendered a missing child only picks it up on its next reactive re-render.

## Mounting and lifecycle

Mounting is implemented as a [UI addition](ui-js-uiadditions.md). Since `ui.additions.applyTo()` already runs after the initial page load, after **every PJAX navigation**, and over every injected fragment (modals, stream entries, widget reloads, comment inserts), Vue islands inherit all of these code paths without any special handling. **PJAX is thereby solved by construction, not worked around.**

The mounter matches two selectors:

1. **Component tags** — the kebab-case form of every registered component name:

   ```html
   <like-button content-id="42"></like-button>
   ```

   Note the HTML parser lowercases tag and attribute names in server-rendered markup — `<LikeButton contentId="42">` only works *inside* Vue templates (which Vue compiles itself); in PHP views the kebab-case form is required.

2. **`[data-vue-component]`** as an explicit fallback for dynamic cases:

   ```html
   <div data-vue-component="LikeButton" data-props='{"contentId":42}'></div>
   ```

Each match becomes its own `createApp()` instance sharing a common plugin set (registry components, i18n, config, error handler).

**Props**

- Simple scalars as individual attributes, kebab-case in HTML, mapped to camelCase and type-coerced (`Number`, `Boolean`) using the component's prop declarations.
- Complex data as a single JSON attribute (`props` on component tags, `data-props` on the fallback form); individual attributes override JSON keys.
- **Initial state travels with the HTML.** A like button receives its current count and liked-state as props — no extra request on page load, no flash of empty content.
- Any markup inside the tag acts as a loading placeholder and is replaced on mount.

**Unmounting** is double-secured:

- On PJAX navigation the existing `unload()` lifecycle unmounts all apps rooted inside `#layout-content`.
- A `MutationObserver` safety net unmounts any app whose root node leaves the DOM (modal closed, stream entry deleted).

No leaked apps, no zombie state, no work for component authors.

**i18n preloading:** a component may declare required message categories (`i18nCategories: ['LikeModule.base']`); the mounter preloads them through `humhub.i18n` before mounting, mirroring `requiredI18nCategories` of classic modules.

## Using components from PHP

Two equivalent ways:

```php
<?php /* a) plain tag — the module's Vue asset bundle must be registered on the page */ ?>
<like-button content-id="<?= $content->id ?>"></like-button>

<?php /* b) widget helper — registers the asset bundle automatically, encodes props */ ?>
<?= VueComponent::widget([
    'name' => 'LikeButton',
    'props' => ['contentId' => $content->id],
]) ?>
```

`VueComponent::widget()` renders the tag form, JSON-encodes non-scalar props, and registers the asset bundle passed via `assetBundle`. No inline `<script>` per instance is ever emitted — props live in attributes, which keeps CSP nonces and PJAX re-execution out of the picture.

**Reserved prop names:** the client-side registry never reads the attributes `class`, `id`, `style`, `props` or anything starting with `data-` as props — `VueComponent` therefore throws when a prop maps onto one of them, or when a prop collides with an entry in `options`. Prop keys must be static, developer-controlled strings.

**Migration mechanism:** existing PHP widgets keep their public API and simply render an island internally. `LikeLink::widget(['object' => $post])` continues to work in every theme and module — `LikeLink::run()` returns `VueComponent::widget([...])` directly, with no PHP view in between (the earlier `views/likeLink.php` was removed). Callers never notice the switch.

**Guest states.** Islands that need to know whether the current visitor is logged in do not receive a `guest` prop from the server — they read it client-side from the `user` `registerJsConfig` section (`isGuest`, `loginUrl`, both populated by `CoreJsConfig` from `Yii::$app->user->isGuest` / `Yii::$app->user->loginUrl`) via `getConfig('user')` from `@humhub/vue`. `LikeButton` is the reference example: guests get the like **count**, non-interactively, plus a link that opens the login modal (`data-bs-target="#globalModal"`, same delegated handler as the user-list link) instead of the like/unlike controls.

## Bridge layer: composables

Vue components reach platform services through composables that delegate to the existing infrastructure — nothing is reimplemented:

| Composable | Delegates to |
|---|---|
| `useI18n('LikeModule.base')` | `humhub.i18n` — ICU MessageFormat, localStorage cache; the message extractor learns to parse `.vue` files |
| `useClient()` | `humhub.client` — CSRF, status handling, redirects |
| `useModal()` | the existing global modal system (open/close); a native `<HModal>` follows later |
| `useConfig('like')` | values passed via `registerJsConfig` |
| `useEvents()` | the global `humhub.event` bus, with automatic unsubscribe on unmount — communication between islands and with legacy JS |

Errors thrown in components hit a global Vue `errorHandler` wired to `humhub.log` and the existing status bar — one consistent error UX.

`url(route, params)` builds URLs for default-routed module endpoints (e.g. `url('/like/like/like', { recordId: 7 })`) against a template registered once, platform-wide, via `CoreJsConfig` (`Url::to(['/__route__'])`, passed through `registerJsConfig` under `url.template`) — pretty URLs yield `<baseUrl>/__route__`, otherwise `<baseUrl>/index.php?r=__route__`; the client fills in the route and appends `params` as a query string. This covers the common case of an island calling back into its own module's controller actions without a server round trip per link. It is **not** a router — there are no client-side route definitions, no navigation, no history handling; anything beyond filling in this one template (custom routes, non-default URL rules) still has its URL generated server-side and passed as a prop or config value, as before.

`url` in `@humhub/vue` is a thin delegate to the standalone core JS module `humhub.url`, so legacy (non-Vue) code can build the same URLs via `require('url').to(route, params)` from any `humhub.module()` — the Vue bridge does not implement its own URL logic anymore.

**Message extraction convention.** `php yii message/extract-module` parses `.vue` files, but only the full call form `i18n.t('Category', 'Message')` (typically in computed properties or methods) is recognized — category-bound helpers hide the category from the extractor. Templates should reference those computed labels instead of calling `t()` inline.

**Preloading vs. server labels.** Declared `i18nCategories` are loaded *before* mount — on a cold cache (and always in debug mode, since the localStorage translation cache is bypassed there, so the preload XHR fires on every page load) that delays the island behind a translation request. The LikeButton pilot uses this path: it declares `i18nCategories: ['LikeModule.base']` and calls `i18n.t('LikeModule.base', 'Like')` from a computed property, with no labels passed from PHP at all. It also demonstrates the complementary optimization for *state* rather than labels: `likeCount`/`currentUserLiked` are optional props — when the server already computed them (the normal case, e.g. on a stream page) no request happens, and the component only calls its own `/like/like/info` endpoint when they are omitted. When both are omitted, the two round trips are **not** parallel: the i18n preload gates the mount itself (see "Mounting and lifecycle" above), and only once mounted does `created()` run and kick off the state fetch — so a fully stateless, cold-cache mount pays for the translation request and *then* the info request, serially. Passing initial state as props (as the PHP widget normally does) avoids the second request entirely; passing server-rendered labels too would avoid the first. For components where the translation round trip itself is FOUC-critical, server-rendered labels passed as props remain the documented alternative; reserve client-side i18n preloading for components with many or dynamic messages, or where avoiding it isn't worth the extra props.

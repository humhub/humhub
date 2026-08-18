# Vue.js Integration (Concept)

> **Status: concept — Phase 1 implemented** (Vue runtime, `humhub.vue` registry/mounter, build tooling, `VueComponent` widget, LikeButton pilot). Later phases (extension slots, base component library, dynamic imports, CI enforcement) are still design-level. This document defines the target architecture for integrating Vue.js into HumHub as an island framework on top of the existing JavaScript layer ([overview](ui-js-overview.md)).

## Motivation

HumHub renders its UI server-side through Yii views and widgets, with interactivity added by the jQuery-based `humhub.module` system. For rich, state-heavy components (comment sections, pickers, choosers) this model leads to complex imperative DOM code and server round-trips for every partial update.

The goal is to gradually replace server-side rendered interactive components with Vue.js components — starting with small leaves like the like button and growing into larger sections like comments — while keeping everything else exactly as it is.

## Goals and non-goals

**Goals**

- Individual Vue components ("islands") embedded in server-rendered pages — including nesting across module boundaries (e.g. a like button inside the comment section).
- Full integration with the existing platform: PJAX navigation, AJAX-injected content (modals, stream), `registerJsConfig`, the client i18n layer, asset bundles, themes.
- Extensible by modules: any module can ship its own components and hook into extension points of other modules' components.
- **No npm/node requirement for installing or running HumHub, and none for module developers who don't touch `.vue` files.** Compiled artifacts are committed to the repository; only developers actively editing Vue sources run a watcher.
- A shared Vue runtime loaded exactly once; module components loaded only on pages that use them.

**Non-goals**

- No single-page application. There is no client-side router; navigation stays with Yii/PJAX.
- No Vue server-side rendering / hydration. Islands render client-side; initial data is embedded in the HTML so no extra requests are needed on page load.
- No replacement of the existing `humhub.module` system. Legacy JS and Vue islands coexist indefinitely and communicate over the existing event bus.

## Constraints

Two platform constraints shape the whole design:

1. **CSP without `unsafe-eval`.** HumHub's Content-Security-Policy forbids runtime code generation in all rule sets. Vue's runtime template compiler generates render code as strings and evaluates them at runtime, which the policy blocks — it is therefore unusable. Only the **runtime-only** build of Vue can ship, which means all templates must be compiled ahead of time — `.vue` single-file components (SFCs) require a compile step by definition. The question is not *whether* to build, only *where and when*.
2. **The production pipeline has no JS bundler.** `grunt build-assets` (`php yii asset`) concatenates and minifies finished JavaScript files; it cannot compile SFCs. Module developers today need zero npm tooling — a property worth preserving.

Consequence: **compiled artifacts are committed** (see [Build tooling](#build-tooling)), exactly like the pre-built richtext editor is consumed today. The asset pipeline keeps treating them as plain JS files.

## Architecture overview

```
┌────────────────────────────────────────────────────────────┐
│ Server-rendered page (Yii views, widgets, themes)          │
│                                                            │
│   <like-button content-id="42"></like-button>   ← island   │
│   <comment-section ...>                          ← island   │
│      └─ <LikeButton> inside its Vue template               │
└────────────────────────────────────────────────────────────┘
             ▲ mounts / unmounts
┌────────────────────────────────────────────────────────────┐
│ humhub.vue (core JS module)                                │
│   component registry · island mounter · lifecycle ·        │
│   composables bridging to client/i18n/modal/event/log      │
└────────────────────────────────────────────────────────────┘
             ▲ registers components
┌────────────────────────────────────────────────────────────┐
│ Per-module artifacts (committed, plain JS asset bundles)   │
│   resources/js/humhub.<module>.vue.js                      │
└────────────────────────────────────────────────────────────┘
             ▲ external/global
┌────────────────────────────────────────────────────────────┐
│ Vue 3 runtime-only (loaded once via core bundle)           │
└────────────────────────────────────────────────────────────┘
```

## Loading and bundling

- **Vue runtime:** Vue 3, runtime-only build, added like all frontend libraries via Composer/asset-packagist (`npm-asset/vue`). A `VueAsset` bundle serves `vue.runtime.global.js` in debug mode (warnings, Vue Devtools support) and `vue.runtime.global.prod.js` otherwise. It becomes part of the core bundle, so every module asset bundle (which implicitly depends on `CoreBundleAsset`) is guaranteed to load after it.
- **`humhub.vue` core module:** a new `protected/humhub/resources/js/humhub/humhub.vue.js`, part of the core JS list like `humhub.i18n.js`. Exposes the registry, the mounter and the composables under `humhub.modules.vue`.
- **Module components:** each module ships one committed artifact (`resources/js/humhub.<module>.vue.js`) registered through a normal `AssetBundle`. Yii's per-page asset registration is the lazy-loading mechanism — a module's Vue code only loads on pages that render one of its components. Nothing loads globally except the runtime and `humhub.vue`.
- **Later stage (optional):** on-demand loading via dynamic `import()` at first mount for heavy components. The registry API already anticipates this (a component may be registered as an async loader), but the first iteration relies on asset bundles only.

## Component registry

Modules register components by name:

```js
import { register } from '@humhub/vue';
import LikeButton from './LikeButton.vue';

register('LikeButton', LikeButton);
```

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

**Migration mechanism:** existing PHP widgets keep their public API and simply render an island internally. `LikeLink::widget(['object' => $post])` continues to work in every theme and module — its view just emits `<like-button ...>` instead of server-rendered markup. Callers never notice the switch.

## Module file layout

```
protected/humhub/modules/like/
├── assets/
│   └── LikeVueAsset.php              # AssetBundle → js/humhub.like.vue.js
├── vue/                              # sources — plain source code like views/, never published
│   ├── index.js                      # entry: imports SFCs, registers components
│   └── LikeButton.vue
└── resources/
    └── js/
        ├── humhub.like.vue.js        # committed build artifact
        └── humhub.like.vue.js.map
```

`vue/index.js` is the single build entry per module:

```js
import { register } from '@humhub/vue';
import LikeButton from './LikeButton.vue';

register('LikeButton', LikeButton);
```

The `@humhub/vue` import is developer-experience sugar only: the build marks it external and maps it onto the `humhub.modules.vue` global — nothing gets bundled twice.

External modules use the identical layout relative to their module root.

## Build tooling

Core ships a zero-config build command (esbuild-based with the Vue SFC plugin; exact tool pinned by core so output stays deterministic). Module developers never write build configuration.

- `build` compiles `vue/index.js` → `resources/js/humhub.<module>.vue.js` (IIFE, Vue and `@humhub/vue` as externals), unminified with a sourcemap. Artifacts are served as standalone published files (they are not part of the compiled core bundles), so they ship unminified by default — `--minify` is available; folding core-module artifacts into the production bundle pipeline is a follow-up decision.
- `watch` recompiles on save (~tens of milliseconds) — the one extra step for developers actively working on `.vue` files.
- `<style>` blocks of SFCs are **extracted into a CSS artifact** (`resources/js/humhub.<module>.vue.css`, listed in the same asset bundle) instead of runtime style injection — themable, cacheable, and no CSP `style-src` relaxation needed.
- **Artifacts are committed.** Installing or running HumHub — and reviewing a module PR — requires no npm. A CI check rebuilds and fails on diff, guarding against stale or hand-edited artifacts.
- Vue sources live at the module root (`vue/`), outside the published `resources/` tree — they can never end up in the web-accessible assets directory, no publish exclusions needed.

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

URLs are generated server-side and passed as props or config, as today. There is deliberately no client-side URL builder or router — that would be the SPA path this concept excludes.

**Message extraction convention.** `php yii message/extract-module` parses `.vue` files, but only the full call form `i18n.t('Category', 'Message')` (typically in computed properties or methods) is recognized — category-bound helpers hide the category from the extractor. Templates should reference those computed labels instead of calling `t()` inline.

**Preloading vs. server labels.** Declared `i18nCategories` are loaded *before* mount — on a cold cache (and always in debug mode) that delays the island behind a translation request. For small leaf components, prefer passing server-rendered labels as props (as the LikeButton pilot does) and reserve client-side i18n for components with many or dynamic messages.

**Base component library.** A gradually growing set of platform components in HumHub markup and theme styling: `HButton`, `HDropdown`, form components (`HForm`, inputs with server-side validation error display), `HTimeAgo`, `HUserImage`, … Phase 1 contains only what the pilot components need; the library grows with real usage instead of being designed up front.

## Extension slots

The Vue analog of PHP widget stacks: components render named extension points, other modules hook into them without forking templates.

```html
<!-- inside CommentEntry.vue -->
<ExtensionSlot name="comment.actions" :context="{ comment }" />
```

```js
// another module's entry file
humhub.vue.registerSlotComponent('comment.actions', 'LikeButton');
```

`ExtensionSlot` renders all components registered for its name, passing the context as props. Registration order defines render order, with an optional `sortOrder`.

## Development mode

With `YII_DEBUG` enabled:

- the Vue dev runtime loads (full warnings, **Vue Devtools** work out of the box),
- sourcemaps point into the original `.vue` sources,
- the registry logs verbosely (missing components, name collisions, mount/unmount tracing via `humhub.log`).

Hot module replacement is out of scope initially — `watch` + browser reload keeps the toolchain simple.

## Pilots and migration path

1. **`LikeButton`** — the minimal leaf. Proves registry, tag mounting, `useClient`, `useI18n`, and the PJAX lifecycle end to end, in a component small enough to review in one sitting.
2. **Comment section** — the flagship. Nesting across modules (`LikeButton` inside), forms, modals, extension slots, list state (show-more, collapsed replies).

After the pilots, the working rule is: **new interactive UI is built in Vue; existing server-rendered widgets are migrated opportunistically** — the PHP widget API stays, its internals become an island. Data flows keep using existing controllers (returning JSON), so backends rarely change.

## Open questions

- Exact SFC build tool (esbuild + Vue plugin vs. Vite/Rollup library mode) — decided during implementation of the build command; the committed-artifact contract is independent of it.
- Distribution of the build command to external module developers (npm package vs. invocation from a core checkout).
- Whether `VueComponent` and the PHP-side classes live under `humhub\modules\ui\vue` or `humhub\widgets`.

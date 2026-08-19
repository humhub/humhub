# Vue.js Integration (Concept)

> **Status: concept — Phase 1 and 2 implemented** (Vue runtime, `humhub.vue` registry/mounter, build tooling, `VueComponent` widget, LikeButton pilot; comment section island with legacy-widget form interop and live updates; extension slots, wired into the comment island's `comment.controls`/`comment.links`). Later phases (base component library, dynamic imports, CI enforcement) are still design-level. This document defines the target architecture for integrating Vue.js into HumHub as an island framework on top of the existing JavaScript layer ([overview](ui-js-overview.md)).

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

At the core, every component is registered by name through a small API:

```js
import { register } from '@humhub/vue';
import LikeButton from './LikeButton.vue';

register('LikeButton', LikeButton);
```

Modules normally never write this code themselves — the filename convention (see [Module file layout](#module-file-layout)) generates it for every top-level component. The explicit `register()` call remains the underlying API for two cases: a `vue/index.js` with custom registration logic, and registering components at runtime (e.g. `registerSlotComponent`, see [Extension slots](#extension-slots)).

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

## Build tooling

Core ships a zero-config build command (Vite library mode with the official Vue SFC plugin; versions are pinned through the repo lockfile so output stays deterministic). Module developers never write build configuration.

- `build` compiles a module's `vue/` sources → `resources/js/humhub.<module>.vue.js` (IIFE, Vue and `@humhub/vue` as externals), unminified with a sourcemap. The entry is generated from the filename convention above unless `vue/index.js` exists, in which case that file is used verbatim. Artifacts are served as standalone published files (they are not part of the compiled core bundles), so they ship unminified by default — `--minify` is available; folding core-module artifacts into the production bundle pipeline is a follow-up decision.
- `watch` recompiles on save (~tens of milliseconds) — the one extra step for developers actively working on `.vue` files.
- After every successful build (initial or, in `--watch` mode, each rebuild), the module's `resources/` and `resources/js/` directories have their mtime bumped to now — Yii's published-asset hash is derived from the source directory's path *and mtime*, not the bytes of the files inside it, so rewriting only the artifact file would otherwise keep serving the previously-published (stale) copy indefinitely in dev; this touch is metadata-only and does not affect artifact byte-reproducibility.
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

`url(route, params)` builds URLs for default-routed module endpoints (e.g. `url('/like/like/like', { recordId: 7 })`) against a template registered once, platform-wide, via `CoreJsConfig` (`Url::to(['/__route__'])`, passed through `registerJsConfig` under `url.template`) — pretty URLs yield `<baseUrl>/__route__`, otherwise `<baseUrl>/index.php?r=__route__`; the client fills in the route and appends `params` as a query string. This covers the common case of an island calling back into its own module's controller actions without a server round trip per link. It is **not** a router — there are no client-side route definitions, no navigation, no history handling; anything beyond filling in this one template (custom routes, non-default URL rules) still has its URL generated server-side and passed as a prop or config value, as before.

`url` in `@humhub/vue` is a thin delegate to the standalone core JS module `humhub.url`, so legacy (non-Vue) code can build the same URLs via `require('url').to(route, params)` from any `humhub.module()` — the Vue bridge does not implement its own URL logic anymore.

**Message extraction convention.** `php yii message/extract-module` parses `.vue` files, but only the full call form `i18n.t('Category', 'Message')` (typically in computed properties or methods) is recognized — category-bound helpers hide the category from the extractor. Templates should reference those computed labels instead of calling `t()` inline.

**Preloading vs. server labels.** Declared `i18nCategories` are loaded *before* mount — on a cold cache (and always in debug mode, since the localStorage translation cache is bypassed there, so the preload XHR fires on every page load) that delays the island behind a translation request. The LikeButton pilot uses this path: it declares `i18nCategories: ['LikeModule.base']` and calls `i18n.t('LikeModule.base', 'Like')` from a computed property, with no labels passed from PHP at all. It also demonstrates the complementary optimization for *state* rather than labels: `likeCount`/`currentUserLiked` are optional props — when the server already computed them (the normal case, e.g. on a stream page) no request happens, and the component only calls its own `/like/like/info` endpoint when they are omitted. When both are omitted, the two round trips are **not** parallel: the i18n preload gates the mount itself (see "Mounting and lifecycle" above), and only once mounted does `created()` run and kick off the state fetch — so a fully stateless, cold-cache mount pays for the translation request and *then* the info request, serially. Passing initial state as props (as the PHP widget normally does) avoids the second request entirely; passing server-rendered labels too would avoid the first. For components where the translation round trip itself is FOUC-critical, server-rendered labels passed as props remain the documented alternative; reserve client-side i18n preloading for components with many or dynamic messages, or where avoiding it isn't worth the extra props.

**Base component library.** A gradually growing set of platform components in HumHub markup and theme styling: `HButton`, `HDropdown`, form components (`HForm`, inputs with server-side validation error display), `HTimeAgo`, `HUserImage`, … Phase 1 contains only what the pilot components need; the library grows with real usage instead of being designed up front.

## Extension slots

The Vue analog of PHP widget stacks: a host component renders a named extension point via `<ExtensionSlot>`, and other modules hook into it by name without forking the host's template — the same relationship `\humhub\widgets\BaseStack` subclasses have to the widgets they stack, translated to islands.

```html
<!-- inside CommentEntry.vue -->
<ExtensionSlot name="comment.links" :context="{ comment }" />
```

```js
// another module's own vue/index.js (see "Module file layout" above)
import { register, registerSlotComponent } from '@humhub/vue';
import ReactionLink from './ReactionLink.vue';

register('ReactionLink', ReactionLink);
registerSlotComponent('comment.links', 'ReactionLink', { sortOrder: 150 });
```

`ExtensionSlot` renders every component registered for its name (via `registerSlotComponent(slotName, componentName, {sortOrder})`), passing `context` down as props to each. Entries render in `sortOrder` order (default `100`), then registration order for ties. Slot names follow the same `<module>.<region>` convention as the two the comment island exposes today: `comment.controls` (inside the entry's `⋮` dropdown — a registered component owns its own `<li><a class="dropdown-item">…` markup, the same contract the dropdown's core items follow) and `comment.links` (appended after the core Reply/Like links in `.wall-entry-controls`).

**Registration order is unconstrained** — `registerSlotComponent()` does not require `componentName` to be registered yet, and `register()` does not require any slot referencing it to exist yet. Whichever half arrives second, `ExtensionSlot` picks it up reactively (no remount). A slot with nothing registered — or nothing *currently registered* — renders nothing: no placeholder, no warning; modules stay entirely optional.

**Passing data other than props down through context.** A component reached through a slot commonly needs data the host itself doesn't otherwise expose — the comment island solves this on the serializer side with a matching extension point, `CommentJsonService::EVENT_SERIALIZE_COMMENTS` (a `SerializeCommentsEvent`, fired once per serialized batch — a window of comments, or a single create/update/info response). A module attaches in its `config.php` and reads the result back out of `context.comment.extensions` on the JS side:

```php
// a module's config.php
'events' => [
    [CommentJsonService::class, CommentJsonService::EVENT_SERIALIZE_COMMENTS, [Events::class, 'onSerializeComments']],
],
```

```php
// the module's Events.php
public static function onSerializeComments(SerializeCommentsEvent $event): void
{
    foreach ($event->comments as $comment) {
        $event->addData($comment->id, 'reportcontent', ['reported' => ReportContent::isReported($comment)]);
    }
}
```

```vue
<!-- ReactionLink.vue -->
<template>
    <a href="#" @click.prevent="onClick">{{ label }}<span v-if="comment.extensions.reportcontent?.reported"> (reported)</span></a>
</template>
<script>
export default {
    props: { comment: { type: Object, required: true } },
    /* ... */
};
</script>
```

Each serialized comment carries the accumulated result under its own `extensions` key, namespaced by the attaching module (`{}` when nothing attached anything) — one query for the whole batch rather than one per comment.

## Development mode

With `YII_DEBUG` enabled:

- the Vue dev runtime loads (full warnings, **Vue Devtools** work out of the box),
- sourcemaps point into the original `.vue` sources,
- the registry logs verbosely (missing components, name collisions, mount/unmount tracing via `humhub.log`).

Hot module replacement is out of scope initially — `watch` + browser reload keeps the toolchain simple.

## Pilots and migration path

1. **`LikeButton`** — the minimal leaf. Proves registry, tag mounting, `useClient`, `useI18n`, and the PJAX lifecycle end to end, in a component small enough to review in one sitting.
2. **Comment section** — the flagship, implemented. `humhub\modules\comment\widgets\Comments`
   renders a `<comment-section>` island (`CommentVueAsset`, depending on `LikeVueAsset` so
   `<LikeButton>` nesting resolves) fed by `CommentJsonService`'s serialized window - no
   comment HTML is server-rendered anymore. It exercises every piece of the architecture
   this document describes: cross-module nesting (`<LikeButton>` inside `CommentEntry.vue`),
   list state (show-more both directions with real counts, one level of collapsed replies),
   the `modal`/`events` bridges (delete confirm, admin-delete, live updates via
   `humhub:modules:comment:live:NewComment`), and a form. The form is the interesting case:
   the richtext editor and file upload are deep jQuery widgets, not rewritten in Vue - see
   "Legacy-widget interop" below for the pattern that makes them work inside an island
   without server-rendering a form per instance. `CommentLink` (the "Comment (n)" link/count
   badge in a wall entry) intentionally stays a plain PHP widget with a tiny
   `humhub.comment.js` bridge (`toggleComment` dispatches a DOM CustomEvent the island
   listens for) - islandizing it too is a possible future step, not required for the pattern
   to hold.

After the pilots, the working rule is: **new interactive UI is built in Vue; existing server-rendered widgets are migrated opportunistically** — the PHP widget API stays, its internals become an island. Data flows keep using existing controllers (returning JSON), so backends rarely change.

## Legacy-widget interop (form shells)

Some server-rendered widgets are too deep to rewrite in Vue in one pass (rich text editors,
file uploaders with drag/drop, progress and previews). The comment form is the reference
example for wrapping one inside an island instead:

1. A PHP widget (`humhub\modules\comment\widgets\CommentFormShell`) renders the widget
   markup exactly as before, but every element id it declares or references (`id`, `for`,
   and CSS-id-selector fragments embedded in `data-*` attribute values) is built from one
   literal placeholder token instead of a real id.
2. That HTML string travels once in the island's initial props (e.g. `formShellHtml`).
3. A small wrapper component (`LegacyFormWrapper.vue`) binds it with `v-html` plus
   `v-additions` (booting the legacy widgets the same way any other injected fragment does),
   after replacing every occurrence of the token with a unique id from a module-scope
   counter - so the SAME shell can be cloned as many times on the page as needed (a create
   form, several open reply forms, an edit form) without id collisions.
4. The wrapper exposes a small, typed API (`getValue()`/`setValue()`/`clear()`/`focus()`/
   `getFileGuids()`) that reads the booted widget instances directly off their own cached
   jQuery data (`Component.prototype.init`'s `this.$.data(this.static('component'), this)`
   key) instead of exporting jQuery/legacy globals to the rest of the Vue tree.

This keeps the deep interactive editing surface exactly as-is while the surrounding
list/state/mutation logic is plain, testable Vue.

## Open questions

- Distribution of the build command to external module developers (npm package vs. invocation from a core checkout).

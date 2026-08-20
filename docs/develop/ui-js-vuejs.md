# Vue.js Integration (Concept)

> **Status: concept — Phase 1 and 2 implemented** (Vue runtime, `humhub.vue` registry/mounter, build tooling, `VueComponent` widget, LikeButton pilot; comment section island with legacy-widget form interop and live updates; extension slots and menu entries, wired into the comment island's `comment.links` slot and `comment.controls` menu respectively). Later phases (base component library, dynamic imports, CI enforcement) are still design-level. This document defines the target architecture for integrating Vue.js into HumHub as an island framework on top of the existing JavaScript layer ([overview](ui-js-overview.md)).

## Chapters

This document covers motivation, goals, constraints and the overall architecture — the conceptual anchor for the rest of the design, which is split into focused chapters:

- [Components](ui-js-vuejs-components.md) — authoring and using components: module file layout, the core component set, the registry, mounting, the `VueComponent` PHP widget, and the composable bridge into existing platform services.
- [Build tooling](ui-js-vuejs-build.md) — `grunt build-vue`/`watch`/`minify`, the committed-artifact contract, development mode, and the vitest test infrastructure.
- [Extending islands](ui-js-vuejs-extensions.md) — extension slots, menu entries, the serializer extension event pattern, domain events, and migrating a legacy widget-stack extension.
- [Legacy interop](ui-js-vuejs-interop.md) — `v-additions`, `RichTextOutput`, `LegacyFormWrapper`, the form-shell pattern, and other patterns for bridging into pre-existing jQuery widgets.

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

Consequence: **compiled artifacts are committed** (see [Build tooling](ui-js-vuejs-build.md#build-tooling)), exactly like the pre-built richtext editor is consumed today. The asset pipeline keeps treating them as plain JS files.

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
   [Legacy interop: the form-shell pattern](ui-js-vuejs-interop.md#form-shell-pattern-vueformshell)
   for the pattern that makes them work inside an island
   without server-rendering a form per instance. `CommentLink` (the "Comment (n)" link/count
   badge in a wall entry) intentionally stays a plain PHP widget with a tiny
   `humhub.comment.js` bridge (`toggleComment` dispatches a DOM CustomEvent the island
   listens for) - islandizing it too is a possible future step, not required for the pattern
   to hold.

After the pilots, the working rule is: **new interactive UI is built in Vue; existing server-rendered widgets are migrated opportunistically** — the PHP widget API stays, its internals become an island. Data flows keep using existing controllers (returning JSON), so backends rarely change.

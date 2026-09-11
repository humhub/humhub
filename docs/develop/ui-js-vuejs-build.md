# Vue.js Build Tooling

> Part of the [Vue.js integration](ui-js-vuejs.md) documentation. This chapter covers the build command that turns a module's `.vue` sources into a committed artifact, the guarantees that artifact must uphold, the dev-mode runtime, and the vitest infrastructure the test suite runs on. For motivation, goals, constraints and the overall architecture, see the [overview](ui-js-vuejs.md).

## Build tooling

Core ships a zero-config build command (Vite library mode with the official Vue SFC plugin; versions are pinned through the repo lockfile so output stays deterministic). Module developers never write build configuration.

- `build` compiles a module's `vue/` sources → `resources/js/humhub.<module>.vue.js` (IIFE, Vue and `@humhub/vue` as externals), unminified with a sourcemap. The entry is generated from the filename convention described in [Components: module file layout](ui-js-vuejs-components.md#module-file-layout) unless `vue/index.js` exists, in which case that file is used verbatim. Artifacts are served as standalone published files (they are not part of the compiled core bundles), so they ship unminified by default — `--minify` is available; folding core-module artifacts into the production bundle pipeline is a follow-up decision.
- Every artifact carries a generated header comment (`/*! AUTO-GENERATED FILE — do not edit. Compiled from <module>/vue/ via \`grunt build-vue --module=<id>\`. See docs/develop/ui-js-vuejs.md */`), marked as a "legal comment" so it survives both `--minify` (esbuild) and the core asset pipeline's own uglify step. Deliberately no timestamp in the banner — the artifact must stay byte-reproducible.
- `watch` recompiles on save (~tens of milliseconds) — the one extra step for developers actively working on `.vue` files.
- After every successful build (initial or, in `--watch` mode, each rebuild), the module's `resources/` and `resources/js/` directories have their mtime bumped to now — Yii's published-asset hash is derived from the source directory's path *and mtime*, not the bytes of the files inside it, so rewriting only the artifact file would otherwise keep serving the previously-published (stale) copy indefinitely in dev; this touch is metadata-only and does not affect artifact byte-reproducibility.
- `<style>` blocks of SFCs are **extracted into a CSS artifact** (`resources/js/humhub.<module>.vue.css`, listed in the same asset bundle) instead of runtime style injection — themable, cacheable, and no CSP `style-src` relaxation needed.
- **Artifacts are committed.** Installing or running HumHub — and reviewing a module PR — requires no npm. The `JavaScript Tests` workflow (`.github/workflows/js-test.yml`) runs the vitest suite, rebuilds every artifact (`node vue.build.mjs --module all`) and fails when the working tree is no longer clean — a stale or hand-edited artifact is otherwise invisible in review, since the source change looks complete while the browser keeps running the old component. It also fails for an artifact that was never `git add`ed at all.
- Vue sources live at the module root (`vue/`), outside the published `resources/` tree — they can never end up in the web-accessible assets directory, no publish exclusions needed.
- `--module all` builds core plus every core module that has Vue sources, in one run — what a change to a shared component or to the core bridge needs, and what CI rebuilds. Targets are discovered, not listed, so a new island is covered by existing. Not combinable with `--watch`.
- `--module core` is a special case: it builds the core framework's own shared components (see [Components: core component set](ui-js-vuejs-components.md#core-component-set)) from `protected/humhub/vue/` into `protected/humhub/resources/js/humhub.core.vue.js`, rather than a module under `protected/humhub/modules/`.

## Development mode

With `YII_DEBUG` enabled:

- the Vue dev runtime loads (full warnings, **Vue Devtools** work out of the box),
- sourcemaps point into the original `.vue` sources,
- the registry logs verbosely (missing components, name collisions, mount/unmount tracing via `humhub.log`).

Hot module replacement is out of scope initially — `watch` + browser reload keeps the toolchain simple.

## Vitest test infrastructure

Component and bridge logic is covered by a `vitest` suite under `protected/humhub/tests/js/`, run via:

```bash
npx vitest run
# or
grunt test-js
```

- **Harness** (`protected/humhub/tests/js/support/setup.mjs`, wired in as vitest's global setup): emulates the browser environment `humhub.vue.js` runs in — the `humhub.module()` registration API and the modules it `require()`s, plus the `jQuery`/`Vue` globals normally provided by asset bundles. `harness.test.js` exercises the harness itself (stub globals present, `humhub.module()` registration works) as a sanity check that the rest of the suite builds on.
- **Shim** (`protected/humhub/tests/js/support/humhubVueShim.mjs`): the test-side stand-in for the `@humhub/vue` import used in Vue sources. The production build maps that import onto the `humhub.modules.vue` global (see [Components: bridge layer](ui-js-vuejs-components.md#bridge-layer-composables)); the shim does the same, lazily, so import order does not matter in tests. `vitest.config.mjs` aliases `@humhub/vue` to this file, and aliases the `vue` import itself to Vue's runtime-only ESM build — mirroring production, where the template compiler is unavailable (see [Constraints](ui-js-vuejs.md#constraints)) — so a template accidentally sneaking into a test component fails in the test run instead of only in the browser.
- **Coverage** spans the registry and mounter (`vue.test.js`), the composable bridge (`bridge.test.js`), the URL builder (`url.test.js`), core components (`coreInterop.test.js`, `dropdownMenu.test.js`), extension slots (`extensionSlot.test.js`, `slotRegistry.test.js`), and the comment island (`commentSection.test.js`, `commentBridge.test.js`, `commentMutations.test.js`, `likeButton.test.js`).

## Open questions

- Distribution of the build command to external module developers (npm package vs. invocation from a core checkout).

# Asset building

How HumHub's frontend assets get from source files in the repository to the right URL in the browser. This page is the deep dive; for the day-to-day commands see [intro-build.md](intro-build.md).

HumHub builds on Yii's [asset bundle](https://www.yiiframework.com/doc/guide/2.0/en/structure-assets) system. Most of the moving parts are upstream — the HumHub specifics are summarised at the end.

## The pieces

- **Source tree**: `protected/humhub/resources/` — committed JS, CSS, fonts and images that ship with the core. Not web-accessible.
- **AssetBundle classes**: `protected/humhub/assets/*.php`. Each one declares a `sourcePath` and a list of `$js`/`$css` files relative to it. Almost all of them point at `@humhub/resources`.
- **AssetManager** (`humhub\components\assets\AssetManager`): copies asset bundles into a web-accessible location at runtime, returns the public URL for each file. Backed by HumHub's filesystem mount system, so the published files can live on the local filesystem (default `webroot/assets/`) *or* on S3-compatible storage / a CDN.
- **The build**: `grunt build-assets` → `php yii asset` combines and compresses bundles into two output files (`humhub-app.{js,css}` and `humhub-bundle.{js,css}`) and writes a runtime config to `assets-prod.php`.
- **Runtime configuration**: `protected/humhub/config/assets-{dev,prod}.php`. `common.php` loads one based on the environment. Bundles defined here override the class defaults.

## Runtime flow

When a view registers an asset bundle (e.g. `AppAsset::register($view)`), Yii's view layer asks `AssetManager` to load it. If the bundle has a `sourcePath` and no `basePath`/`baseUrl`, the manager:

1. Hashes the `sourcePath` to a stable directory name (so the URL is the same on every host).
2. Copies the tree into the assets mount (`webroot/assets/<hash>/` by default).
3. Sets `basePath`/`baseUrl` on the bundle to that location.
4. Caches the mapping so subsequent requests don't re-check the filesystem.

The view then emits `<link>`/`<script>` tags using `baseUrl + filename`.

`AssetManager::clear()` (invoked from *Admin → Flush caches*) wipes the published directories so the next request republishes. The `_published` cache is cleared at the same time.

### Dev vs production mode

- **Dev mode** (`HUMHUB_DEBUG=true` → `YII_ENV='dev'`) loads `assets-dev.php`. Bundles register their individual `$js`/`$css` files; the browser fetches dozens of small files. Source maps and original filenames stay intact.
- **Production / test mode** loads `assets-prod.php`. Most bundle classes get neutralised (their `$js`/`$css` become `[]`) and they depend on the two combined target bundles — so the page loads exactly two JS files and two CSS files.

Switching modes never requires a rebuild as long as the production bundle has already been built once.

## The build

`grunt build-assets` runs:

```sh
mkdir -p protected/humhub/resources/build
rm -rf protected/humhub/resources/build/*/
cd protected
php yii asset humhub/config/assets.php humhub/config/assets-prod.php
php yii cache/flush-all
```

`php yii asset` (Yii's [asset console command](https://www.yiiframework.com/doc/guide/2.0/en/structure-assets#combining-compressing-assets)) reads the build config (`assets.php`), processes the listed bundles, and writes the runtime config (`assets-prod.php`).

### What the build does

1. **Instantiate every dependency bundle** listed under `bundles`.
2. **Publish each one** through the build-time `AssetManager`. For npm/vendor sources this means a real copy into the build-time `basePath`; for sources already inside the source tree it's a no-op (see *Build-time tree handling* below).
3. **Combine + compress** the JS files of every bundle owned by a target into one output (`humhub-app.js`, `humhub-bundle.js`), invoking the configured `jsCompressor` / `cssCompressor` — both wired to Grunt tasks (`uglify:assets`, `cssmin`).
4. **Rewrite CSS `url(...)` references**. For every CSS file being combined, Yii recomputes each `url(...)` so it resolves correctly *from the output file's location*. The math is filesystem-based: it knows where the input CSS lives (because the source bundle was just published) and where the output CSS will be written (`target.basePath`), and produces a relative URL between them.
5. **Write `assets-prod.php`**. The targets get registered as runnable asset bundles; the dependencies they swallow get their `$js`/`$css` emptied and rewired to depend on the target.

### Build config

`protected/humhub/config/assets.php` controls the build. The important knobs:

- `bundles` — every dependency bundle that should end up inside a target. The two app entry points (`AppAsset`, `CoreBundleAsset`) are *not* listed here; only their `STATIC_DEPENDS`.
- `targets` — the output bundles. Each one declares:
  - `sourcePath = '@humhub/resources'` — where the bundle source tree lives. Tells the runtime to publish it via AssetManager.
  - `basePath = '@humhub/resources'` — where the build writes the compressed output (`js/humhub-app.js` etc.) and the reference point for CSS URL rewriting.
  - `depends` — the bundles whose JS/CSS should be combined into this target.
- `assetManager` — the build-time asset manager. Its `basePath = '@humhub/resources/build'` is where external dependencies get published during the build.

## Self-contained resources tree

The above means `@humhub/resources/` ends up self-contained after a build:

```
protected/humhub/resources/
├── css/
│   ├── humhub-app.css        ← built; references ../build/<hash>/...
│   ├── humhub-bundle.css     ← built
│   └── ...source CSS files...
├── js/
│   ├── humhub-app.js         ← built
│   ├── humhub-bundle.js      ← built
│   └── ...source JS files...
├── img/                      ← committed images
├── scss/                     ← excluded from publish via publishOptions
└── build/                    ← published external dependencies
    ├── 1a9ffd08/             ← FontAwesome (sourcePath = @npm/font-awesome)
    │   ├── css/
    │   └── fonts/
    ├── 841b69df/             ← Animate.css
    └── ...one hash dir per external dep...
```

Every `url(...)` inside the bundled CSS is tree-relative (`url(../build/1a9ffd08/fonts/fa.woff)`). When `AssetBundle::publish()` later copies the whole tree to `webroot/assets/<hash>/` (or to the CDN root, if the assets mount is S3), the relative positions are preserved and every URL still resolves.

This is the property that makes the build robust: there are no absolute paths and no host-specific assumptions baked into the bundle.

## HumHub-specific extensions

Three pieces in core deviate from a vanilla Yii setup.

### `humhub\components\assets\AssetManager` (runtime)

The production AssetManager swaps Yii's local filesystem operations for HumHub's [filesystem mount system](concept-files.md) (Flysystem-backed). The same publishing flow works whether the assets mount is local, on S3, or on a remote object store. Configure the mount via `HUMHUB_CONFIG__COMPONENTS__FS__MOUNT_ASSETS` and the corresponding mount definitions in `common.php`.

`clear()` empties the mount on cache flush. There's no special-casing: a fresh publish on the next page request rebuilds everything.

### `humhub\components\assets\BuildAssetManager` (build only)

Plugged in via `assets.php`'s `'assetManager' => ['class' => BuildAssetManager::class, ...]`. Solves a chicken-and-egg problem during the build:

- Build-time `assetManager.basePath = '@humhub/resources/build'`.
- ~17 bundle classes declare `sourcePath = '@humhub/resources'`.
- Publishing them via Yii's default manager would call `copyDirectory('@humhub/resources', '@humhub/resources/build/<hash>')` — copying a directory into a sub-directory of itself. Yii's `FileHelper::copyDirectory()` rejects this.

The override short-circuits publish whenever the source path already lives inside `@humhub/resources`: no copy needed, the files are already in the tree. Yii's URL rewriting (filesystem-based) handles them correctly from their original location. External sources (npm/vendor) fall through to `parent::publish()` and land in `@humhub/resources/build/<hash>/` as expected — this is the directory referenced from the bundled CSS via tree-relative `../build/<hash>/...` URLs.

### `humhub\commands\AssetController` (build only)

Wired in via `controllerMap` in `console.php`. Solves a quirk of Yii's `saveTargets()`:

When writing `assets-prod.php`, Yii forces `'sourcePath' => null` on target bundles and copies the build-time `basePath`/`baseUrl` into the file. The assumption — valid in plain Yii setups — is that built bundles are served straight from their build location. In our case the build location is `@humhub/resources`, which is *not* web-accessible.

Our override re-reads the generated file after `parent::saveTargets()`, restores `sourcePath` on each target that had one, and strips `basePath`/`baseUrl`. The runtime bundle therefore has only `sourcePath` set, which triggers a regular AssetManager publish on first use — same path the runtime takes for any other asset bundle.

The `controllerMap` entry is required because Yii's `coreCommands()` hard-codes `asset` to its own controller; `controllerNamespace` alone isn't enough to override it.

## Adding a new bundled dependency

1. Create the AssetBundle class under `protected/humhub/assets/`. Point `$sourcePath` at the package (e.g. `@npm/some-package` or `@vendor/foo/bar`).
2. Add the class name to `AppAsset::STATIC_DEPENDS` (loaded eagerly) or `CoreBundleAsset::STATIC_DEPENDS` (deferred).
3. Run `grunt build-assets`. The new dependency gets published into `protected/humhub/resources/build/<hash>/` and its JS/CSS gets combined into the appropriate output bundle.
4. Commit the new files under `protected/humhub/resources/{build,js,css}/` together with the AssetBundle class.

Dependencies that already live inside `@humhub/resources` (i.e. shipping their JS/CSS as part of the core source tree) don't need step 1 — they only need to be added to `STATIC_DEPENDS`.

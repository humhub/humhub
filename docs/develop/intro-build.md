# Build

HumHub bundles a few [Grunt](https://gruntjs.com/) tasks that wrap common console commands — asset builds, migrations, search-index rebuilds, the test server. Run them from the repository root.

## Setup

1. Install [Node.js](https://nodejs.org/en/download/package-manager/)
2. Install the Grunt CLI globally:

   ```sh
   npm install -g grunt-cli
   ```

3. Install local dependencies in the HumHub root:

   ```sh
   npm install
   ```

## Build production assets

HumHub uses Yii's built-in [asset combining and compression](https://www.yiiframework.com/doc/guide/2.0/en/structure-assets#combining-compressing-assets). Compressed assets are only served in [production mode](advanced-security.md#enable-production-mode) and during [acceptance tests](intro-testing.md#run-acceptance-tests). Debug mode serves the source files separately so they can be inspected in the browser.

A git-based install does *not* ship pre-built production assets — you have to build them manually before running in production mode or running acceptance tests. The build writes:

- `protected/humhub/resources/js/humhub-app.js` and `humhub-bundle.js`
- `protected/humhub/resources/css/humhub-app.css` and `humhub-bundle.css`
- `protected/humhub/resources/build/<hash>/...` — external dependencies (FontAwesome, jQuery, Bootstrap, …)

At runtime the whole `protected/humhub/resources` tree gets published into the assets mount (`webroot/assets/` by default).

### Grunt task (recommended)

```sh
grunt build-assets
```

The task clears `protected/humhub/resources/build/*`, runs the asset compiler, and flushes the cache.

### Manual build

```sh
rm -rf protected/humhub/resources/build/*/
cd protected
php yii asset humhub/config/assets.php humhub/config/assets-prod.php
php yii cache/flush-all
```

See the [Yii Asset Guide](https://www.yiiframework.com/doc/guide/2.0/en/structure-assets#combining-compressing-assets) for what the compiler does upstream, and [Asset building](internals-assets.md) for HumHub's wiring on top.

## Rebuild search index

Rebuilds the [search index](concept-search.md):

```sh
grunt build-search
```

## Run migrations

Apply all pending [database migrations](concept-models.md#scheme-updates) for the core *and* enabled modules:

```sh
grunt migrate-up
```

Core migrations only:

```sh
grunt migrate-up --module=0
```

## Tests

Start the test web server (`php -S localhost:8080 index-test.php`):

```sh
grunt test-server
```

Run the test suite:

```sh
grunt test
```

See the [testing guide](intro-testing.md) for filtering by module, running specific suites, and acceptance-test setup.

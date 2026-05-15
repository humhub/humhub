# HumHub Build

HumHub provides some [grunt](https://gruntjs.com/) tasks to ease the execution of some console commands. This guide describes how to setup
grunt and run these commands. All grunt tasks need to be run within your HumHub root.

## Setup Grunt 

 1. [Install Node.js](https://nodejs.org/en/download/package-manager/)
 2. [Install Grunt CLI](https://gruntjs.com/using-the-cli)
 
```console
npm install -g grunt-cli
```

 3. call `npm install` in your HumHub root

## Build production assets

HumHub uses Yii`s build-in mechanism for [compressing and combining assets](https://www.yiiframework.com/doc/guide/2.0/en/structure-assets#combining-compressing-assets)
as javascript or stylesheet files in combination with grunt. Those compressed assets are only used when running 
in [production mode](advanced-security.md#enable-production-mode) and in [acceptance tests](intro-testing.md#run-acceptance-tests).
When running in debug mode, separate assets files are used to ease development and debugging.

When running a [development environment](intro-environment.md#gitcomposer-installation), you'll have to manually build those
production assets in order to run acceptance tests (or testing the production mode). The production build will compress
scripts and stylesheets configured in `humhub\assets\AppAsset` into the following files:

- `@humhub/static/js/all-*.js`
- `@humhub/static/css/all-*.css`

#### Grunt based build

The simples way to build your production assets is by running the following grunt task:

```console
grunt build-assets
```

#### Manual build

1. Delete the content of your `static/assets` directory.
2. Delete the old compressed file `@humhub/static/js/all-*.js` and `@humhub/static/css/all-*.css`
2. Run the following command within your `protected` directory:

```console
php yii asset humhub/config/assets.php humhub/config/assets-prod.php
```

Refer to the [Yii Asset Guide](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html#combining-compressing-assets)
for more information.

## Search index rebuild

Grunt task for rebuilding your [Search Index](concept-search.md)

```console
grunt build-search
```

## Run migration

Grunt task for running database [migrations](concept-models.md#scheme-updates).

The following command will run the migration for core and modules:

```console
grunt migrate-up
```

Run core migrations only:

```console
grunt migrate-up --module=0
```
## Testing

Run test server:

```console
grunt test-server
```

Run tests:

```console
grunt test
```
See [Testing Guide](intro-testing.md) for more use cases of the `grunt test` task.

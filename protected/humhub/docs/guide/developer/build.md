HumHub Build
============

HumHub provides some [grunt](https://gruntjs.com/) tasks to ease the execution of some console commands. This guide describes how to setup
the grunt and use the available commands.

## Grunt Setup

 - [Install Node.js](https://nodejs.org/en/download/package-manager/)
 - [Install Grunt CLI](https://gruntjs.com/using-the-cli)
 
```
npm install -g grunt-cli
```

 - call `npm install` in your HumHub root

## Build Assets

HumHub uses Yii`s build-in mechanism for compressing and combining assets as javascript or stylesheet files in combination with grunt.
Those compressed assets are only used when running in [production mode](admin-installation.md#disable-errors-debugging) and in [acceptance tests](testing.md).

Your production assets are saved under `/humhub/js/all-*.js` respectively `static/css/all-*.css`.

> Note: Only [[humhub\assets\AppAsset]] dependencies are compressed.

- Grunt Asset Built

The simples way to build your production assets is using the following grunt task:

```
grunt build-assets
```

- Manual Asset Built

1. Delete the content of your `static/assets` directory.
2. Delete the old compressed file `static/js/all-*.js` and `static/css/all-*.css`
2. Run:

```
php yii asset humhub/config/assets.php humhub/config/assets-prod.php
```

> Info: Detailed information is available in the [Yii Asset Guide](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html#combining-compressing-assets).

## Build Community Theme

- Install [Less](http://lesscss.org/usage/)

```
npm install less -g
```

- Grunt theme build

```
grunt build-theme
```

to build another theme within the `@humhub/themes` directory run:

```
grunt build-theme --name=MyTheme
```

- Manual theme build

```
lessc -x themes/HumHub/less/build.less themes/HumHub/css/theme.css
```

### Other Grunt Tasks

 - `grunt build-search` Rebuild your [Search Index](../admin/search.md)
 - `grunt migrate-up` Runs all missing core and module [migrations](models.md#scheme-updates)
 - `grunt migrate-create --name=my_migration` Creates a new [migration](models.md#scheme-updates) within the `@humhub/migrations` directory
 - `grunt test` and `grunt testServer` [Testing Guide](testing.md)

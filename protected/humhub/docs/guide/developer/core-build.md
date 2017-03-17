HumHub Build
============

## Setup

 1. Install NPM
 2. Install Grunt (http://gruntjs.com/getting-started)
 3. call `npm update` in humhub root

> Note: Since symlinks are not supported in some virtual machine shared folders the update command should be called from the host.

## Setup grunt dependencies

Call the following commands in your humhub root directory:
 - `npm update`
 - `npm install grunt --save-dev`

## Build Assets

HumHub uses Yiis build in mechanism for compressing and combining assets as javascript or stylesheet files in combination with grunt.

Your compressed files will be saved under `/humhub/js/all-*.js` respectively `static/css/all-*.css`.

> Note: HumHub will only use the compressed assets if operated in [production mode](admin-installation.md#disable-errors-debugging), otherwise
all assets will be served seperatly.

### Grunt Asset Built

The simples way to build your production assets is using the following grunt task:

```
grunt build-assets
```

### Manual Asset Built

1. Delete the content of your `static/assets` directory.
2. Delete the old compressed file `static/js/all-*.js` and `static/css/all-*.css`
2. Run:

```
php yii asset humhub/config/assets.php humhub/config/assets-prod.php
```

> Info: More information is available in the [Yii Asset Guide](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html#combining-compressing-assets).

## Build Community Theme

To rebuild the community themes  `theme.css` file you can execute one of the following commands:

```
lessc -x themes/HumHub/less/build.less themes/HumHub/css/theme.css
```

or with grunt:

```
grunt build-theme
```

### Other Grunt Tasks
 - `grunt build-search` Rebuild your [Search Index](../admin/search.md)


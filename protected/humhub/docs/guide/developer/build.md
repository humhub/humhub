Build
========

## Setup
 - Install NPM
 - Install Grunt (http://gruntjs.com/getting-started)
 - call npm update in humhub root

> Note: Since symlinks are not supported in some virtual machine shared folders the update command should be called from the host

## Setup grunt dependencies
 - npm update (in humhub root)
 - npm install grunt --save-dev

## Assets

HumHub uses Yii's build in mechanism for compressing and combining assets as javascript or stylesheet files in combination with grunt.
HumHub will only use the compressed assets if operated in [production mode](admin-installation.md#disable-errors-debugging), otherwise
all assets are included seperatly.

The compressed production assets are build by calling:

```
yii asset humhub/config/assets.php humhub/config/assets-prod.php
```

This will create the following files:

 - /humhub/js/all-*.js - compressed js file with all core libraries
 - /humhub/css/all-*.css - compressed css files with all core stylesheets

More information is available on http://www.yiiframework.com/doc-2.0/guide-structure-assets.html#combining-compressing-assets

### Grunt Tasks
 - watch
 - uglify
 - cssmin
(TBD)

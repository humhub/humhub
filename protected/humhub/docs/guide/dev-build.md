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
 - Yii asset management http://www.yiiframework.com/doc-2.0/guide-structure-assets.html#combining-compressing-assets
 - php yii asset humhub/config/assets.php humhub/config/assets-prod.php

### Grunt Tasks
 - watch
 - uglify
 - cssmin
(TBD)

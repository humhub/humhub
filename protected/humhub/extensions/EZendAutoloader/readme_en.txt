Fast Zend Framework class loader for Yii
========================================

Allows to efficiently load Zend Framework classes without need to additionally call
require_once() or Yii::import().

Installation
------------

### Extension
Unpack extension to `protected/extensions/`.

### Zend Framework
Copy ZF directory `Zend` to `protected/vendors/`.
You can use [special service to get only required
ZF classes](http://epic.codeutopia.net/pack/).

### Stripping all require_once from ZF
It is required for this extension and allows to load ZF classes faster.

You can do search-replace from your IDE: search `require_once` replace to `//require_once`.

Altenatively you can use console:

GNU:

~~~
% cd path/to/ZendFramework/library
% find . -name '*.php' -not -wholename '*/Loader/Autoloader.php' \
  -not -wholename '*/Application.php' -print0 | \
  xargs -0 sed --regexp-extended --in-place 's/(require_once)/\/\/ \1/g'
~~~

MacOSX:

~~~
% cd path/to/ZendFramework/library
% find . -name '*.php' | grep -v './Loader/Autoloader.php' | \
xargs sed -E -i~ 's/(require_once)/\/\/ \1/g'
% find . -name '*.php~' | xargs rm -f
~~~

### Updating your index.php

We need to register loader before application is run:

~~~
[php]
define('YII_DEBUG', true);
$webRoot=dirname(__FILE__);
require_once(dirname($webRoot).'/framework/yii.php');
$configFile=$webRoot.'/../protected/config/main.php';
$app = Yii::createWebApplication($configFile);

// you can load not only Zend classes but also other classes with the same naming
// convention
EZendAutoloader::$prefixes = array('Zend', 'Custom');

Yii::import("ext.yiiext.components.zendAutoloader.EZendAutoloader", true);
Yii::registerAutoloader(array("EZendAutoloader", "loadClass"), true);

$app->run();
~~~
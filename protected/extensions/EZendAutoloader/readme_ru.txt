Быстрый загрузчик классов Zend Framework для Yii
================================================

Позволяет максимально быстро загружать классы Zend Framework без использования
require_once() или Yii::import().

Установка
---------

### Расширение
Распаковываем расширение в `protected/extensions/`.

### Zend Framework
Копируем директорию `Zend` фреймворка в `protected/vendors/`.
Также можно воспользоваться [сервисом, вырезающим из ZF необходимый
функционал](http://epic.codeutopia.net/pack/).

### Вырезаем require_once из ZF
Это необходимо для работы расширения и позволяет быстрее загружать классы ZF.

Через поиск-замену в IDE: `require_once` заменяем на `//require_once`.

Если у вас есть под рукой консоль, то можно воспользоваться её возможностями:

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

### Изменяем index.php

До запуска приложения необходимо зарегистрировать загрузчик:
~~~
[php]
define('YII_DEBUG', true);
$webRoot=dirname(__FILE__);
require_once(dirname($webRoot).'/framework/yii.php');
$configFile=$webRoot.'/../protected/config/main.php';
$app = Yii::createWebApplication($configFile);

Yii::import("ext.yiiext.components.zendAutoloader.EZendAutoloader", true);

// you are able to load custom code that is using Zend class naming convention
// with different prefix
EZendAutoloader::$prefixes = array('Zend', 'Custom');
Yii::registerAutoloader(array("EZendAutoloader", "loadClass"), true);

$app->run();
~~~
Security
========

* Make sure that Paths /protected, /uploads/file are not accessible by web (.htaccess)
* Comment out following lines in ``index.php`` to disable error reporting / debugging.
```php
//defined('YII_DEBUG') or define('YII_DEBUG', true);
//defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 5);
//ini_set('error_reporting', E_ALL);
```

Security
========

Disable Errors / Debugging
--------------------------

 - Modify *index.php* in your humhub root directory
     
```php
[...]
// comment out the following two lines when deployed to production
// defined('YII_DEBUG') or define('YII_DEBUG', true);
// defined('YII_ENV') or define('YII_ENV', 'dev');
[...]
```

 - Delete *index-test.php* in your humhub root directory if exists


Protected Directories
---------------------

Make sure following directories are not accessible by web:
- protected
- uploads/file

By default these folders are protected with a ".htaccess" file.


Limit User Access
-----------------

TBD

Keep up with the latest HumHub version
---------------------------------------

TBD



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

If you're running a private social network, make sure the user registration is disabled or the approval system for new users is enabled.

- Disable user registration: `Administration -> Users -> Settings -> Anonymous users can register`
- Enable user approvals: `Administration -> Users -> Settings -> Require group admin approval after registration`
- Make sure guest access is disabled: `Administration -> Users -> Settings -> Allow limited access for non-authenticated users (guests)`

Keep up with the latest HumHub version
---------------------------------------

As admin you'll receive a notification when a new HumHub version is released. We recommend to always use the latest stable version.

We take security very seriously and continuously improving the security features of HumHub. 




Security
========

Enable Production Mode
--------------------------

By default HumHub is operating in _DEBUG_ mode, which besides others uses a different error handling and non combined
assets. Before opening your installation to the public you should enable the production mode first by commenting out the
following lines of the `index.php` file within your HumHub root directory:

```php
[...]
// comment out the following two lines when deployed to production
// defined('YII_DEBUG') or define('YII_DEBUG', true);
// defined('YII_ENV') or define('YII_ENV', 'dev');
[...]
```

> Note: In this example the lines are already commented out.

You should also delete the `index-test.php` file in your HumHub root directory if existing.

Protected Directories
---------------------

Please make sure you followed the directory permissions described in the [Installation Guide](installation.md#file-permissions)!

Limit User Access
-----------------

If you're running a private social network, make sure the user registration has been disabled or the approval system for new users has been enabled.

- Disable user registration: `Administration -> Users -> Settings -> Anonymous users can register`
- Enable user approvals: `Administration -> Users -> Settings -> Require group admin approval after registration`
- Make sure guest access is disabled: `Administration -> Users -> Settings -> Allow limited access for non-authenticated users (guests)`

Keep HumHub Up-To-Date 
---------------------------------------

As an admin you'll receive notifications about new HumHub releases. We strongly recommend to always update to the latest stable version if possible.
Check the [automatic](updating-automatic.md) or [manual](updating.md) update guide for more information about updating your HumHub installation.

Furthermore, you should regularly check the `Administration -> Modules -> Available Updates` section for module updates. 

We take security very seriously, and we're continuously improving the security features of HumHub. 

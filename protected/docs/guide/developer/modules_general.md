Modules - General
=================

See http://www.yiiframework.com/doc/guide/1.1/en/basics.module for general information
about modules in Yii. 

As different modules could be dynamically loaded and enabled there is no need
to add them into a global configuration file like main.php.

Module Folder Structure
------------------------

```
/protected/
    /modules/                           
        /mymodule/                      - Module Folder
            module.json                 - Meta Information about this module (name, version & co.)
            autostart.php               - Information about Id, BaseClass, Events and Imports 
            MyModule.php                - Base Module Class inherit from HWebModule
            /views/                     - Views Folder
            /controllers/               - Controllers Folder
            /models/                    - Models Folder
            ...
```



autostart.php
-------------

Each module requires a ``autostart.php`` File which registers the module to the main application.
``Note:`` Contents of autostart.php are cached in file /protected/runtime/cache_autostart.php - delete this file or flush caches after modifing this file!

__Example of a autostart.php File__

```php
    <?php
    Yii::app()->moduleManager->register(array(

        // Unique ID of the module, same as the module folder
        'id' => 'example',

        // Module Base Class (http://www.yiiframework.com/doc/guide/1.1/en/basics.module)
        'class' => 'application.modules.example.ExampleModule',

        // Optional Section: Global Imports 
        'import' => array(
            'application.modules.example.*',
            [...]
        ),

        // Optional Section: Events to catch when module is enabled
        // Use this to modify e.g. menus 
        // http://www.yiiframework.com/doc/guide/1.1/en/basics.component#event
        'events' => array(
            // Listen for onInit Event of AdminMenuWidget and sent to 
            // Module Class File to handle it
            array('class' => 'AdminMenuWidget', 'event' => 'onInit', 
                  'callback' => array('ExampleModule', 'onAdminMenuInit')),
            
            [...]
        ),

    ));
    ?>
```


module.json
-----------

This file holds basic information about the module like name, description or
current version.

__Example of a ´´module.json´´ File__
```
    {
        "id": "mymoduleid",
        "name": "My Module",
        "description": "My testing module.",
        "keywords": ["my", "cool", "module"],
        "version": "1.0",
        "humhub": {
            "minVersion": "0.6"
            "maxVersion": "1.0"
        }
    }
```
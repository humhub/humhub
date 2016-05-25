Modules - Getting Started
=================

Basically modules in HumHub are identical to Yii2 modules [http://www.yiiframework.com/doc-2.0/guide-structure-modules.html](http://www.yiiframework.com/doc-2.0/guide-structure-modules.html).

You can use either the Yii's module base class [[yii\base\Module]] or the enhanced HumHub module base class [[humhub\components\Module]].

The enhanced HumHub module class provides additional features like:
- Dynamic module management (enable / disable / install / uninstall) via administration interface
- Usable as Space or User Profile module

## Quick start with HumHub Module Generator
You can use the HumHub module generator to quickly create a basic HumHub module for you to build on top of.

### Enabling the Module Generator
The module generator extends Gii, a code generation tool for the Yii framework. As such, you must enable Gii and configure it to use HumHub's generators.

#### Web
Add the following to your web configuration (/protected/config/web.php) to enable the generator on the Gii web UI.
```php
return [
    // ...
    'modules' => [
        // ...
        'gii' => [
            'class' => 'yii\gii\Module',
            'allowedIPs' => ['127.0.0.1', '::1'],
            'generators' => [
                'module' => [
                    'class' => 'humhub\generators\module\Generator',
                    'templates' => [
                        'humhub' => '@humhub/generators/module/default',
                    ]
                ]
            ],
        ],
        // ...
    ]
];
```
You can then access Gii through the following URL:
```
http://localhost/path/to/index.php?r=gii
```
Or if you have enabled pretty URLs, you may use the following URL:
```
http://localhost/path/to/index.php/gii
```

You can then click the "Start" button for the HumHub Module Generator.



#### Console
Add the following to your console configuration (/protected/config/console.php) to enable the generator in your console.
```php
return [
    // ...
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => [
            'class' => 'yii\gii\Module',
            'allowedIPs' => ['127.0.0.1', '::1'],

            'generators' => [
                'module' => [
                    'class' => 'humhub\generators\module\Generator',
                    'templates' => [
                        'humhub' => '@humhub/generators/module/default',
                    ]
                ]
            ],
        ],
    ],
    // ...
];
```
You can run the HumHub Module Generator via the command line as follows:
```
cd protected
php yii gii/module --moduleClass="app\modules\example\Module" --moduleID=example --template="humhub"
```



## config.php


If the module is placed inside the */protected/modules* folder, you can create a *config.php* in the module directory which provides automatic loading without manually modifing the application config.

The config.php should return an array including following fields:

- **id** - Unqiue ID of the module (required)
- **class** - Namespaced classname of the module (required)
- **events** - Array of Events (optional)
- **namespace** - Namespace of your module 
- **urlManagerRules** - Array of URL Manager Rules  [http://www.yiiframework.com/doc-2.0/yii-web-urlmanager.html#addRules()-detail](http://www.yiiframework.com/doc-2.0/yii-web-urlmanager.html#addRules()-detail)
- **modules** - Submodules (optional)

Example of a config.php file:

```php
<?php

use johndoe\example\Module;
use humhub\widgets\TopMenu;

return [
    'id' => 'example',
    'class' => 'johndoe\example\Module',
    'namespace' => 'johndoe\example',
    'events' => [
        array('class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => array('johndoe\example\Module', 'onTopMenuInit')),
    ]
];
?>
```

> Note: Do not execute any code in the __config.php__ - the result will be cached!


## module.json

This file holds basic information about the module like name, description or current version. Locate this file in the root directory of the module.

```
{
    "id": "example",
    "name": "My Example Module",
    "description": "My testing module.",
    "keywords": ["my", "cool", "module"],
    "version": "1.0",
    "humhub": {
    "minVersion": "0.20"
    }
}
```
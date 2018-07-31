Module Developement
=================

The following guide describes the basic module structure and extended module features as well as important considerations regarding your own custom modules.
Since HumHub is based on the [Yii Application Framework](http://www.yiiframework.com/doc-2.0) you should at least be familiar with the basic concepts of this framework
before writing your own code as:

 - [Basic Application Structure](https://www.yiiframework.com/doc/guide/2.0/en/structure-overview)
 - [Controllers](https://www.yiiframework.com/doc/guide/2.0/en/structure-controllers)
 - [Models](https://www.yiiframework.com/doc/guide/2.0/en/structure-models)
 - [Views](https://www.yiiframework.com/doc/guide/2.0/en/structure-views)

You should also follow the [Coding Standards](coding-standards.md) and keep an eye on the [Migration Guide](modules-migrate.md) in order to
keep your module compatible with new HumHub versions and facilitate new features.

## Introduction

Before starting with the development of your custom module, first consider the following **module options**:

- Can my module be [enabled on user and/or space level](#use-of-contentcontainermodule)?
- Does my module produce [content](content.md)?
- Does my module produce [stream entries](stream.md)?
- Does my module add any [sidebar snippets](snippet.md)?
- Do I need to [extend or change the default behaviour](module-change-behavior.md) of core components?
- Do I need specific [permissions](permissions.md) for my module?
- Does my module create any [notifications](notifications.md) or [activities](activities.md)?
- Should [guest users](permissions.md#guest-access) have access to some parts of my module?

Furthermore you may have to consider the following **issues**:

- [Module settings and configuration](settings.md)
- [Append a module to a specific navigation](module-change-behavior.md#extend-menus)
- [Client side developement](javascript-index.md)
- [Schema Migrations and Integrity](models.md)
- [Testing](testing.md)
- [File handling](files.md)
- [Events](events.md)
- [Translation](i18n.md)
- [Live UI updates](live.md)
- [Security](security.md)
- [Embedded Themes](../theme/module.md)

## Module Life Cycle

### Install Module

A module is considered as `installed` once it resides in one of the `moduleAutoloadPaths`. By default non core modules reside in `@humhub/protected/modules`.
You can install modules either by adding them manually to an autoload path or by loading them from the marketplace. 

> Info: You can add additional module paths by means of the `moduleAutoloadPaths` parameter. 
Please see the [Developement Environment Section](environment.md#external-modules-directory) for more information.

### Enabled Module

In order to use a module, you'll have to `enable` it first. This can be achieved by:

- Administration Backend `Administration -> Modules`
- Console command `php yii module/enable`

Enabling a module will run the modules [database migrations](models.md#initial-migration) and add an entry to the `modules_enabled` table.

The `ModuleManager` responsible for enabling modules will trigger the following events right before and after enabling a module:

- `ModuleManager::EVENT_BEFORE_MODULE_ENABLE`
- `ModuleManager::EVENT_AFTER_MODULE_ENABLE`

### Bootstrap

During the `bootstrap` phase of the application the [[humhub\components\bootstrap\ModuleAutoLoader]] will search for all `enabled` modules
within the module autoload path and attach the [modules event listeners](#module-events).

### Disable Module

`Disabling` a module will usually drop all related module data from the database and will detach the module from the `bootstrap` process.

Modules can be disabled by means of

- Administration Backend `Administration -> Modules`
- Console command `php yii module/disable`

The `ModuleManager` responsible for disabling modules will trigger the following events right before and after enabling a module:

- `ModuleManager::EVENT_BEFORE_MODULE_DISABLE`
- `ModuleManager::EVENT_AFTER_MODULE_DISABLE`

> Note: [ContentContainerModules](#use-of-contentcontainermodule) also have to be enabled within a space or user profile by means of the space management
section.

### Uninstall Module

`Uninstalling` a module means removing it from the autoload path.

> Warning: You should never delete an enabled module folder manually without disabling it first.

## Basic Module Structure

Basically HumHub modules are identical to [Yii2 modules](http://www.yiiframework.com/doc-2.0/guide-structure-modules.html).

A very basic module consists of the following elements:

```
 controllers/ - contains controller classes
 migrations/  - contains database migration files and uninstall script
 models/      - contains model classes
 views/       - contains the modules view files
 widgets/     - contains widget classes
 Module.php   - the main module class which can contain enable/disable logic for contentcontainer etc.
 config.php   - base module configuration.
 module.json  - module metadata
```

### Basic Module Configuration `config.php`

The `config.php` file enables automatic module loading and event configuration, without the need to manually modify the main application config. 
Module configuration files of enabled modules are processed by the [[humhub\components\bootstrap\ModuleAutoLoader]] within the `bootstrap` process of the application.

The `config.php` should contain the following attributes:

- **id** - Unqiue ID of the module (required)
- **class** - Namespaced classname of the module class (required)
- **namespace** - The namespace of your module (required)
- **events** - Array containing the modules event configuration (optional)
- **urlManagerRules** - Array of [URL Manager Rules](http://www.yiiframework.com/doc-2.0/yii-web-urlmanager.html#addRules()-detail) (optional)
- **modules** - Submodules (optional)

Example:

```php
<?php

use johndoe\example\Module;
use humhub\widgets\TopMenu;

return [
    'id' => 'example',
    'class' => 'johndoe\example\Module',
    'namespace' => 'johndoe\example',
    'events' => [
        ['class' => TopMenu::class, 'event' => TopMenu::EVENT_INIT, 'callback' => ['johndoe\example\Events', 'onTopMenuInit']],
    ]
];
?>
```

> Note: Do not execute any code in the `config.php` since the result will be cached!

#### Module Events

In order to extend or alter the behavior of some features, your module can listen to class level events as for example:

 - **Widget** events
 - **ActiveRecord** validation,save or delete events
 - **Application** events
 - **Controller** events
 
Events are configured within your modules `config.php` file as described in the previous section. Module event handler should ideally reside in an
extra `Events` class, especially if you plan multiple event handlers. In simpler cases events handlers may be implemented directly within the `Module` class
itself.

See [change the default behaviour](module-change-behavior.md) for additional event use-cases.

### Module Classes

The `Module.php` file contains the actual module class which should either extend [[humhub\components\Module]] or [[humhub\modules\content\components\ContentContainerModule]].
The `Module` class provides basic module functions used for disabling and retrieving metadata.

#### Module Class Level Configuration

Public fields of the `Module` class can be overwritten by the application configuration. This can be useful to provide some extra settings.

The following example module defines

```php
namespace  mymodule;

class Module extends \humhub\components\Module
{
    public $maxValue = 200;
    
    // ...
}
```
The `maxValue` can be overwritten by the following settings within the `@humhub/protected/config/common.php`

```php
return [
    'modules' => [
        'mymodule' => [
            'maxValue' => 300
        ]
    ]
]
```

The setting is used within your domain logic as follows:

```php
$maxValue = Yii::$app->getModule('mymodule')->maxValue;
```

While module class level configurations are handy for values which are not changed that often, 
you may should consider using [Settings and Configurations](settings.md) in combination with the `Module::getConfigUrl()`
to implement a admin configuration.

#### Module Settings

The `Module::getConfigUrl()` can be used to set a module configuration view. Once this function is implemented an `Configure`
button will be added to your module within the module overview section.

The controller handling your configuration should extend [[humhub\modules\admin\components\Controller]].
Refer to the [Settings and Configurations](settings.md) in order to learn how to save global or container related settings.

[ContentContainerModules](#use-of-contentcontainermodule) provide a `getContentContainerConfigUrl()` function respectively.

#### Disable Module Logic

The modules `Module::disable()` function is called while disabling the module. Within the disable logic of your module
you should clear all module related database entries. Note that you should iterate over the entries and delete them by means of the
`ActiveRecord::delete()` function in order to trigger ActiveRecord events. Note the `ActiveRecord::deleteAll()` **does
not trigger** those events. 

```php
class Module extends \humhub\components\Module
{
    public function disable()
    {
        // Clear module related contentent etc...
        foreach (MyContentModel::find()->all() as $model) {
            $model->delete();
        }
        
        // Don't forget to call this!!
        parent::disable();
    }
}
```

By default the `disable()` function will clear the following data:

 - Execute your modules `uninstall.php` migration script
 - Clear all `ContentContainerSettings` and global `Settings` related with this module
 - Clear the `module_enabled` entry

See the [Container Module](#container-module) section for information about disabling your module on `ContentContainer` level.

#### Export Module Permissions

Module specific permissions are exported by means of the [[humhub\components\Module::getPermissions()]] function. See the [Permissions](permissions.md) section for more information.

#### Module Assets and `$resourcesPath`

The [[humhub\components\Module::resourcesPath]] defines the modules resource directory, containing images, javascript files or other assets.

### module.json

The `module.json` file holds basic metadata which is used for example by the marketplace.

Example `module.php` file:

```json
{
    "id": "example",
    "name": "My Example Module",
    "description": "My testing module.",
    "keywords": ["my", "cool", "module"],
    "screenshots": ["assets/screen_1.jpg"],
    "version": "1.0",
    "humhub": {
        "minVersion": "1.2"
    }
}
```

- **id** - The module ID
- **name** - The modules name
- **description** - A short module description
- **keywords** - Array of significant keywords
- **screenshots** - Some screenshots for the marketplace, those should reside in the `resourcesPath` of your module.
- **version** - Current module version
- **minVersion** - Defines the minimum HumHub core version this module version is compatible with.

> Warning: You should align the `minVersion` of your module when using new features and test your modules on all supported versions.

## Extended Module Structure

The following structure contains some additional directories and files, which should be added for specific use-cases or features. 

```
 activities     - activity classes
 assets/        - asset bundle classes
 components/    - component and services classes
 controllers/   - see above
 live/          - live event classes
 jobs/          - queue job classes
 messages/      - contains the modules message files
 migrations/    - see above
 models/        - see above
 modules/       - contains any submodules
 notifications/ - notification classes
 permissions/   - permission classes
 resources/     - contains web assets as javascript files or stylesheets
 tests/         - module tests
 views/         - see above
 widgets/       - see above
 Events.php     - is often used for static event handlers
 Module.php     - see above
 config.php     - see above
 module.json    - see above
```

## Use of ContentContainerModule

In case your module can be enabled on space or user account level your `Module` class has to derive from [[humhub\modules\content\components\ContentContainerModule]]. 

`ContentContainerModule` classes provide some additional functions as:

- `getContentContainerTypes()` - defines for which container-type (space or user account) this module can be enabled. 

- `disableContentContainer()` - is called when this module is disabled for a given container.

- `getContentContentContainerDescription()` - provides a general description of this module for a given container.

- `getContentContainerConfigUrl()` - returns an URL linking to a container level configuration

The following example module can be enabled on space and profile level:

```php
namespace mymodule;

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

class Module extends ContentContainerModule
{

    // Defines for which content container type this module can be enabled
    public function getContentContainerTypes()
    {
        // This content container can be assigned to Spaces and User
        return [
            Space::class,
            User::class,
        ];
    }

    // Is called when the whole module is disabled
    public function disable()
    {
        // Clear all Module data and call parent disable!
        parent::disable();
    }

    // Is called when the module is disabled on a specific container
    public function disableContentContainer(ContentContainerActiveRecord $container)
    {
        parent::disableContentContainer($container);
        //Here you can clear all data related to the given container
    }

    // Can be used to define a specific description text for different container types
    public function getContentContainerDescription(ContentContainerActiveRecord $container)
    {
        if ($container instanceof Space) {
            return Yii::t('MyModule.base', 'Description related to spaces.');
        } elseif ($container instanceof User) {
            return Yii::t('MyModule.base', 'Description related to user.');
        }
    }
}
```

Globally enabled `ContentContainerModules` can be enabled on the container within the **User Account Module Settings** or **Space Module Settings** (depending
on the `getContentContainerTypes()` return value), which will add an `contentcontainer_module` table entry.

By default the `CotnentContainerModule::disableContentContainer()` clears the following data:

- All cotnainer related [settings](settings.md)

The `CotnentContainerModule::disable()` will
 
 - call `CotnentContainerModule::disableContentContainer()` for each container this module is enabled.
 - clear all `contentcontainer_module` entries related to this module.
 - call `parent::disable()` (see )

The following example shows the usual `disable` logic of a module with an [ContentContainerActiveRecord](content.md#implement-custom-contentactiverecords)

```php
    /**
     * @inheritdoc
     */
    public function disable()
    {
        foreach (Poll::find()->all() as $poll) {
            $poll->delete();
        }

        parent::disable();
    }

    /**
     * @inheritdoc
     */
    public function disableContentContainer(ContentContainerActiveRecord $container)
    {
        parent::disableContentContainer($container);

        foreach (Poll::find()->contentContainer($container)->all() as $poll) {
            $poll->delete();
        }
    }
```

> Info: You may want to use the [devtools Module](https://github.com/humhub/humhub-modules-devtools) to create a module skeleton.
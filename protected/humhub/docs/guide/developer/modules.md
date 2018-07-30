Module Developement - Getting Started
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

Before starting with the development of your custom module, you first have to consider the following **module options**:

- Can my module be [enabled on profile and/or space level](#use-of-contentcontainermodule)?
- Does my module produce [content](content.md)?
- Does my module produce [stream entries](stream.md)?
- Does my module provide any kind of [sidebar snippet](snippet.md)?
- Do I need to [change the default behaviour](module-change-behavior.md) of some core components?
- Do I need specific [permissions](permissions.md) for my module?
- Does my module create any [notifications](notifications.md) or [activities](activities.md)?
- Should [guest users](permissions.md#guests-access) have access to some of my module views and functions?

Furthermore you may have to consider the following **issues**:

- [Module settings and configuration](settings.md)
- [Append my module to a specific navigation](module-change-behavior.md)
- [Client side developement](javascript-index.md)
- [Asset Management](assets.md)
- [Data Integrity](models.md#data-integrity)
- [Migrations and Uninstallation and Compatibility](migration.md)
- [Testing](testing.md)
- [File handling](files.md)
- [Events](events.md)
- [Translation](i18n.md)
- [Live UI updates](live.md)
- [Submodules](#submodules)
- [Security](security.md)
- [Theming](embedded-themes.md)

## Basic Life Cycle

A module is considered as `installed` once it resides within one of the `moduleAutoloadPaths`, by default non core modules reside in `protected/modules`.
You can install modules either by adding them manually to an module autoload path or by loading them from the marketplace. 

In order to use a module, you'll have to `enable` it first. This can be achieved by:

- Administration Backend `Administration -> Modules`
- Console command `php yii module/enable`

Enabling a module will run the modules database migrations in order to setup the database scheme and furthermore adds an entry to the `modules_enabled` table.

The `ModuleManager` responsible for enabling module will furthermore trigger an  Trigger `ModuleManager::EVENT_BEFORE_MODULE_DISABLE` and `ModuleManager::EVENT_BEFORE_MODULE_DISABLE` `ModuleEvent`.

During the `bootstrap` process of the application the [[humhub\components\bootstrap\ModuleAutoLoader]] will search for all `enabled` modules
within the module autoload path and initializes the modules event listeners.

`Disabling` a module will usually drop all related module data from the database and will detach the module from the `bootstrap` process.

Module can be disabled by means of

- Administration Backend `Administration -> Modules`
- Console command `php yii module/disable`

The `ModuleManager` responsible for disabling module will furthermore trigger an  Trigger `ModuleManager::EVENT_BEFORE_MODULE_ENABLE` and `ModuleManager::EVENT_AFTER_MODULE_ENABLE` `ModuleEvent`.

> Note: [ContentContainerModules](#use-of-contentcontainermodule) also have to be enabled within a space or user profile by means of the space management
section.

> Info: You can add additional module paths by means of the `moduleAutoloadPaths` parameter. 
Please see the [Developement Environment Section](environment.md#external-modules-directory) for more information.

> Warning: You should never delete an enabled module folder without disabling it first.


## Basic Module Structure

Basically modules in HumHub are identical to [Yii2 modules](http://www.yiiframework.com/doc-2.0/guide-structure-modules.html).

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

### Basic Module Configuration config.php

The `config.php` file enables automatic module loading and event configuration, without the need to manually modify the main application config, by returning an array including the following fields:
Module configuration files of enabled modules are processed by the [[humhub\components\bootstrap\ModuleAutoLoader]] within the `bootstrap` process of the application.

- **id** - Unqiue ID of the module (required)
- **class** - Namespaced classname of the module class (required)
- **namespace** - The namespace of your module (required)
- **events** - Array containing the modules event configuration (optional)
- **urlManagerRules** - Array of [URL Manager Rules](http://www.yiiframework.com/doc-2.0/yii-web-urlmanager.html#addRules()-detail) (optional)
- **modules** - Submodules (optional)

Example `config.php` file:

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

In order to extend or alter the behavior of some features, your module can listen to class level events like:

 - **Widget** events
 - **ActiveRecord** validation,save or delete events
 - **Application** events
 - **Controller** events
 - And other custom events
 
Events are configured within your modules `config.php` file as in the previous example. Module event handler should ideally reside in an
extra `Events` class, especially if you plan many event handlers. In some simpler cases events handlers are implemented within the `Module` class
itself.

See [change the default behaviour](module-change-behavior.md) for some use-cases of event handlers.

### Module Classes

The `Module.php` file contains the actual module class which should either extend [[humhub\components\Module]] or [[humhub\modules\content\components\ContentContainerModule]].

The base `Module` class provides some basic module functions used for enabling, disabling and retrieving metadata, 
whereas the `ContentContainerModule` class has to be extended in case your module requires to be enabled on space or user account level. 

The Module class is responsible for:

#### Handling the enabling and disabling of the module

The modules `disable()` function is called if the module is disabled.

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

> Note: The default implementation of `disable()` will clear some module data automatically as the modules global and ContentContainer settings, profile and space module relations.

#### Handling the enabling and disabling of this module for a given space or profile

See the [Container Module](#container-module) section for more information.

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
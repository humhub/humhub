Module - Getting Started
=================

The following guide describes the basic module structure and extended module features as well as important considerations regarding your own custom module.

## Before starting

Before even starting the developement of a custom module, you first have to consider the following **module options**:

- [Can my module be enabled on profile and/or space level?](modules.md#container-module)
- Does my module produce [stream entries](stream.md) or other [content](content.md)?
- Does my module provide any kind of sidebar [snippet](snippet.md)?
- Do I need to [change the default behaviour](module-change-behavior.md) of some core components?
- Do I need specific [permissions](permissions.md) for my module?
- Does my module create any [notifications](notifications.md) or [activities](activities.md)?
- Should [guest](permissions.md#guests-access) users have access to some of my module views and functions?

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

### config.php

The `config.php` file enables automatic module loading and event configuration, without the need to manually modify the main application config, by returning an array including the following fields:


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
        ['class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => ['johndoe\example\Module', 'onTopMenuInit']],
    ]
];
?>
```

> Note: Do not execute any code in the `config.php` since the result will be cached!


### Module.php

The `Module.php` file contains the actual module class which should either extend the [[humhub\components\Module]] or [[humhub\modules\content\components\ContentContainerModule]] class.


The [[humhub\components\Module|Module]] class provides some basic module functions used for installing/uninstalling and retrieving metadata, whereas the [[humhub\modules\content\components\ContentContainerModule]] class has to be extended in case your module requires to be enabled on space or profile level. 

The Module class is responsible for:

**Handling the enabling and disabling of the module**

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
>Note: The default implementation of `disable()` will clear some module data automatically as the module global and ContentContainer settings, profile/space module relations.

#### Handling the enabling and disabling of this module for a given space or profile
See the [Container Module]() section for more information.

####  Export Module Permissions
Module specific permissions are exported by means of the [[humhub\components\Module::getPermissions()]] function. See the [Permissions]() section for more information.

#### Export Module Notification
Modules can export Notificaions in order to make them configurable in the notificaiton settings.
See the [Notifications]() section for more information.

####  Module Assets and `$resourcesPath`
The [[humhub\components\Module::resourcesPath]] defines the modules resource directory, containing images, javascript files or other assets.

See the [Module Assets]() section for more information.

### module.json

This file holds basic metadata which is for example used by the markeplace.

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
    "minVersion": "0.20"
    }
}
```

- **id** - The module ID
- **name** - The modules name
- **description** - A short module description
- **keywords** - Array of significant keywords
- **screenshots** - Some screenshots for the marketplace
- **version** - Current module version
- **minVersion** - Defines the minimum HumHub core version this module version is compatible with.

## Extended Module Structure

The following structure contains some additional directories and files, which should be added for specific usecases or features. 


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

>Note: the extended module structure and it's directory names is just a recommendation.

## Container Module

In case your module can be enabled on space or profile level your `Module` class has to extend from [[humhub\modules\content\components\ContentContainerModule]]. You should extend this class if your module provides space or profile specific views or content. 

- The `getContentContainerTypes()` method defines for which ContentContainer type (space or profile) this module can be enabled. 

- The `disableContentContainer()` method is called when this module is disabled for a given ContentContainer (Space or Profile).

- The `getContentContentContainerDescription()` method provides a general description of this module for the given ContentContainer.

The following example module can be enabled on space and profile level:
```php
class Module extends \humhub\modules\content\components\ContentContainerModule
{

    // Defines for which content container type this module can be enabled
    public function getContentContainerTypes()
    {
        // This content container can be assigned to Spaces and User
        return [
            Space::className(),
            User::className(),
        ];
    }

    // Is called when the whole module is disabled
    public function disable()
    {
        // Clear all Module data and call parent disable
        [...]
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

> Note: If you're working with content or other persistent data, make sure to delete container related data when the module is disabled on a contentcontainer. This can be archieved by overwriting the [[humhub\modules\content\components\ContentContainerModule::disableContentContainer]] function.

## Creating a Module Template with Gii

(TBD)

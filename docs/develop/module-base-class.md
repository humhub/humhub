# Module Class

## Base Module Class

The main module class defined in `Module.php` implements basic module functionality which will be described in more detail
in the following section. There are two base module classes:

- `humhub\components\Module` - Used for modules which can only be installed on a global level.
- `humhub\modules\content\components\ContentContainerModule` - Used for modules which can also be installed
on space and/or user level.

### Module field configuration

Public fields of the module class can be overwritten by [configuration](https://docs.humhub.org/docs/admin/advanced-configuration). 
This can be useful in order to provide module related configuration options, which are not necessarily 
configurable within the admin interface.

**Example:**

```php
// @mymodule/Module.php
class Module extends \humhub\components\Module
{
    public $maxValue = 200;
    // ...
}
```

The `maxValue` can be overwritten by the following configuration:

```php
// @humhub/protected/config/common.php
return [
    'modules' => [
        'mymodule' => [
            'maxValue' => 300
        ]
    ]
]
```

The configuration value can be read as follows:

```php
$maxValue = Yii::$app->getModule('mymodule')->maxValue;
```

You can also define callback functions, which then can be implemented or overwritten within the configuration.

### `getConfigUrl()`

While module class level configurations are handy for values which are not changed that often, 
you may should consider using [module settings](concept-settings.md) in combination with the `Module::getConfigUrl()` function
to implement module settings configurable within the admin interface.

The `Module::getConfigUrl()` can be used to define a module configuration view. Once this function is implemented a `Configure`
button will be added to your module in the module admin overview.

**Example:**

```php
// @mymodule/Module.php
public function getConfigUrl()
{
    return Url::to(['/mymodule/config/index']);
}
```

Refer to the [Settings and Configurations](concept-settings.md) section to learn how to save and load global or container related settings.
See [getContentContainerConfigUrl()](#getcontentcontainerconfigurl) for providing a container specific config action.

### `getDescription()`

This function should be overwritten to provide a short description of this module which will be displayed in
the module overview. See [getcontentcontainerdescription](#getcontentcontainerdescription) for implementing 
container specific module descriptions.

**Example:**

```php
// @mymodule/Module.php
public function getDescription()
{
    return Yii::t('MyModule.base', 'Adds some special features to your platform.');
}
```

### `disable()`

The `Module::disable()` function is called when the module was disabled. Within the disable logic of your module
you should clear all module related database entries. Note that you should iterate over the entries and delete them by means of the
`ActiveRecord::delete()` function in order to trigger **beforeDelete** and **afterDelete** events. Note the `ActiveRecord::deleteAll()` **does
not trigger** those events. 

```php
// @mymodule/Module.php
public function disable()
{
    // Clear module related content etc...
    foreach (MyContentModel::find()->all() as $model) {
        $model->delete();
    }
    
    // Don't forget to call this!!
    parent::disable();
}
```

By default the `disable()` function will clear the following data:

 - Execute your modules `uninstall.php` migration script
 - Clear all `ContentContainerSettings` and global `Settings` related with this module
 - Clear the `module_enabled` entry

See the [Container Module](#contentcontainermodule) section for information about disabling your module on `ContentContainer` level.

### `getPermissions()`

Module specific permissions are exported by means of the `Module::getPermissions()` function. 
See the [Permissions](concept-permissions.md) section for more information.

### `$resourcesPath`

The `Module::$resourcesPath` defines the modules resource directory, containing images, javascript files or other assets.

**Example:**

```php
// @mymodule/Module.php
class Module extends \humhub\components\Module
{
    public $resourcesPath = 'resources';

    //...
}
```

## ContentContainerModule

The `ContentContainerModule` class needs to be extended for modules which are
installable on space or user profile level. A module can only be enabled on container level
once it is globally [enabled](module-lifecycle.md#enabled-module). Enabling a module on container level will add a relation 
to the `contentcontainer_module`. Note, this is not the case for modules, which are set as default for a given container type.

`ContentContainerModule` extends the base `humhub\components\Module` class
with additional container related functions, which will be described in the following. 

**Example:**

```php
// @mymodule/Module.php
namespace mymodule;

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

class Module extends ContentContainerModule
{
 
    public function getContentContainerTypes()
    {
            // This module can only be installed on spaces
            return [Space::class];
    }
}
```

### `getContentContainerTypes()`

This function needs to be overwritten in order to define on which container types (Space and/or User) this module should
be installable. The following implementation will add the module to the module overview of Spaces and Users.

```php
// @mymodule/Module.php
public function getContentContainerTypes()
{
    // This module can only be installed on Spaces and User level
    return [
        Space::class,
        User::class
    ];
}
```

### `getContentContainerConfigUrl()`

Similar to [getConfigUrl()](#getconfigurl), this function can be used to define a container related config action.

**Example:**

```php
// @mymodule/Module.php
public function getContentContainerConfigUrl(ContentContainerActiveRecord $container)
{
    return $container->createUrl('/mymodule/config/index');
}
```

### `disable()`

In addition to `Module::disable()` the `ContentContainerModule::disable()` function will:

- call `ContentContainerModule::disableContentContainer()` for each container this module is enabled on.
- clear all `contentcontainer_module` entries related to this module.

### `disableContentContainer()`

This function is called after the module was disabled on a specific container. Overwrite this function in case you
need to clear up container related module data as content and other records.

By default, the `disableContentContainer()` clears the following data:

- All container related [settings](concept-settings.md)

**Example:**

```php
// @mymodule/Module.php
public function disableContentContainer(ContentContainerActiveRecord $container)
{
    
    foreach (MyContent::find()->contentContainer($container)->all() as $entry) {
        $entry->delete();
    }
        
    // Don't forget this!
    parent::disableContentContainer($container);
}
```

### `getContainerPermissions()`

Overwrite this function instead of [getPermissions()](#getpermissions) to return permission which are only relevant
for containers this module is enabled on.

**Instead of:**

```php
// @mymodule/Module.php
public function getPermissions($contentContainer = null)
{
    if ($contentContainer && $contentContainer->moduleManager->isEnabled($this->id)) {
        return [
            SomePermission::class
        ]
    }

    return parent::getPermissions($contentContainer);
}
```

**Use this:**

```php
// @mymodule/Module.php
protected function getContainerPermissions($contentContainer = null)
{
    return [
        SomePermission::class
    ]
}
```

### `getGlobalPermissions()`

Overwrite this function in order to define [global module permissions](concept-permissions.md#group-permissions).

**Example:**

```php
// @mymodule/Module.php
protected function getGlobalPermissions()
{
    return [
        SomeGroupPermission::class
    ]
}
```

### `getContentContainerDescription()`

Overwrite this function in case you want to return a more specific description of this module for a given container.
The module description is used within the module overview of a user/space. If not implemented, 
the `Module::getDescription()` will be used for all containers as default.

```php
// @mymodule/Module.php
public function getContentContainerDescription(ContentContainerActiveRecord $container)
{
    if ($container instanceof Space) {
        return Yii::t('MyModule.base', 'Adds some nice space featues.');
    } elseif ($container instanceof User) {
       return Yii::t('MyModule.base', 'Adds some very nice profile featues.');
    }
}
```

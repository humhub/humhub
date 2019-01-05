Module - Base Class
===================

The `Module.php` file contains the actual module class which should either extend [[humhub\components\Module]] or [[humhub\modules\content\components\ContentContainerModule]].
The `Module` class provides basic module functions used for disabling and retrieving metadata.

## Module Class Level Configuration

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
to implement an admin configuration.

## Module Settings

The `Module::getConfigUrl()` can be used to set a module configuration view. Once this function is implemented a `Configure`
button will be added to your module within the module overview section.

Your config controller should extend [[humhub\modules\admin\components\Controller]].
Refer to the [Settings and Configurations](settings.md) in order to learn how to save global or container related settings.

[ContentContainerModules](#use-of-contentcontainermodule) provide a `getContentContainerConfigUrl()` function respectively.

## Disable Module Logic

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

## Permissions

Module specific permissions are exported by means of the [[humhub\components\Module::getPermissions()]] function. See the [Permissions](permissions.md) section for more information.

## Assets and `$resourcesPath`

The [[humhub\components\Module::resourcesPath]] defines the modules resource directory, containing images, javascript files or other assets.


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


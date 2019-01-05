Module Life Cycle
=================

## Bootstrap

During the `bootstrap` phase of the application the [[humhub\components\bootstrap\ModuleAutoLoader]] will search for all `enabled` modules
within the module autoload path and attach the [modules event listeners](events.md).

## Install Module

A module is considered as `installed` once it resides in one of the `moduleAutoloadPaths`. By default non core modules reside in `@humhub/protected/modules`.
You can install modules either by adding them manually to an autoload path or by loading them from the marketplace. 

> Info: You can add additional module paths by means of the `moduleAutoloadPaths` parameter. 
Please see the [Developement Environment Section](environment.md#external-modules-directory) for more information.

## Enabled Module

In order to use a module, you'll have to `enable` it first. This can be achieved by:

- Administration Backend `Administration -> Modules`
- Console command `php yii module/enable`

Enabling a module will run the modules [database migrations](models.md#initial-migration) and add an entry to the `modules_enabled` table.

The `ModuleManager` responsible for enabling modules will trigger the following events right before and after enabling a module:

- `ModuleManager::EVENT_BEFORE_MODULE_ENABLE`
- `ModuleManager::EVENT_AFTER_MODULE_ENABLE`


## Disable Module

`Disabling` a module will usually drop all related module data from the database and will detach the module from the `bootstrap` process.

Modules can be disabled by means of

- Administration Backend `Administration -> Modules`
- Console command `php yii module/disable`

The `ModuleManager` responsible for disabling modules will trigger the following events right before and after enabling a module:

- `ModuleManager::EVENT_BEFORE_MODULE_DISABLE`
- `ModuleManager::EVENT_AFTER_MODULE_DISABLE`

> Note: [ContentContainerModules](modules-base-class.md#use-of-contentcontainermodule) also have to be enabled within a space or user profile by means of the space management
section.

## Uninstall Module

`Uninstalling` a module means removing it from the autoload path.

> Warning: You should never delete an enabled module folder manually without disabling it first.

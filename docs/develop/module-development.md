# Getting Started

The following guide describes the basic module structure and extended module features as well as important considerations regarding your own custom modules. Since HumHub is based on the [Yii Application Framework](http://www.yiiframework.com/doc-2.0) you should at least be familiar with the basic concepts of this framework before writing your own code as:

- [Basic Application Structure](https://www.yiiframework.com/doc/guide/2.0/en/structure-overview)
- [Controllers](https://www.yiiframework.com/doc/guide/2.0/en/structure-controllers)
- [Models](https://www.yiiframework.com/doc/guide/2.0/en/structure-models)
- [Views](https://www.yiiframework.com/doc/guide/2.0/en/structure-views)
- [Assets](https://www.yiiframework.com/doc/guide/2.0/en/structure-assets)

You should also follow the [Coding Standards](intro-coding-standards.md) and keep an eye on the [Migration Guide](https://github.com/humhub/humhub/blob/develop/MIGRATE-DEV.md) in order to keep your module compatible with new HumHub versions and facilitate new features.

## Before you start

Before starting with the development of your custom module, first consider the following module options:

- Can my module be [enabled on user and/or space level](module-base-class.md#contentcontainermodule)?
- Does my module produce [content](concept-content.md)?
- Does my module produce [stream entries](concept-stream.md)?
- Does my module add any [sidebar snippets](ui-snippets.md)?
- Do I need specific [permissions](concept-permissions.md) for my module?
- Does my module create any [notifications](concept-notifications.md) or [activities](concept-activities.md)?
- Should [guest users](concept-permissions.md#guest-access) have access to some parts of my module?

Furthermore, you may have to consider the following issues:

- [Module settings and configuration](concept-settings.md)
- [Append a module to a specific navigation](module-change-behavior.md#extend-menus)
- [Client side development](ui-js-overview.md)
- [Schema Migrations and Integrity](concept-models.md)
- [Testing](intro-testing.md)
- [File handling](concept-files.md)
- [Events](concept-events.md)
- [Translation](concept-i18n.md)
- [Live UI updates](concept-live.md)
- [Security](advanced-security.md)
- [Embedded Themes](https://docs.humhub.org/docs/theme/module)

It's always a good idea to get some inspiration from existing modules which may already solved some of the problems you are facing in your custom module. For example have a look at repositories at:

- [https://github.com/humhub](https://github.com/humhub)
- [https://github.com/humhub-contrib](https://github.com/humhub-contrib)
  :::

## Setup a module skeleton

The easiest way of setting up a basic HumHub module is by using the [Developer Tools Module](https://github.com/humhub/humhub-modules-devtools). Once you've generated a module skeleton, copy the module to a [module loader path](intro-environment.md#module-loader-path). Now the module should be visible under `Administration -> Modules` and can be [enabled](#enabled-a-module).

Alternatively, you can take a look at the following GitHub template project: [Example Module](https://github.com/humhub/example-basic).

## Module Structure

Basically HumHub modules are identical to [Yii2 modules](http://www.yiiframework.com/doc-2.0/guide-structure-modules.html).

A minimal HumHub module at least has to define the following files and metadata:

```
my-module
├── config.php
│   ├── id
│   ├── namespace
│   └── class
├── module.json
│   ├── id
│   ├── name
│   ├── description
│   └── version
└── Module.php
```

### `config.php`

The `config.php` can be used to define event handlers, and the definition of [URL Rules](https://www.yiiframework.com/doc/guide/2.0/en/runtime-routing#creating-rules)
and consists of the following data:

| Attribute              | Description                                                                                                                                         |    
|------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------|
| `id`                   | Unique module ID **(required)**                                                                                                                     | 
| `class`                | Namespaced classname of the module class **(required)**                                                                                             |
| `namespace`            | The namespace of your module **(required)**                                                                                                         |
| `events`               | Array containing the modules event configuration                                                                                                    |
| `urlManagerRules`      | Array of [URL Manager Rules](https://www.yiiframework.com/doc/guide/2.0/en/runtime-routing#creating-rules)                                          |
| `modules`              | Can be used to define submodules                                                                                                                    |
| `consoleControllerMap` | List of console controllers. See also: [Yii2 API](https://www.yiiframework.com/doc/api/2.0/yii-base-module#$controllerMap-detail) **(HumHub 1.7+)** |

**Example:**

```php
// @example/config.php
use humhub\widgets\TopMenu;

return [
    'id' => 'example',
    'class' => 'johndoe\example\Module',
    'namespace' => 'johndoe\example',
    'events' => [
        [
           'class' => TopMenu::class, 'event' => TopMenu::EVENT_INIT, 
           'callback' => ['johndoe\example\Events', 'onTopMenuInit']
        ]
    ],
    'consoleControllerMap' => [
          'example' => 'johndoe\example\console\ExampleController'
    ]
];
```
Do not execute any dynamic code directly within `config.php` since the result will be cached!

Do choose a preferably unique module id which does not interfere with any [core](intro-overview.md#core-modules-and-components)
or other available module.

### `module.json`

The `module.json` file holds basic metadata of a module used by the marketplace.

Available attributes:

| Field         | Description                                                                                          |    
|---------------|------------------------------------------------------------------------------------------------------|
| `id`          | The module ID **(required)**                                                                         | 
| `version`     | The module version. This must follow the format of X.Y.Z. **(required)**                             |
| `name`        | The modules name **(required)**                                                                      |
| `description` | A short module description **(required)**                                                            |
| `humhub`      | HumHub core `minVersion` and `maxVersion` requirements                                               |
| `keywords`    | Module related keywords as string array                                                              |
| `screenshots` | Some screenshots file names for the marketplace, those should reside in the `Module::$resourcesPath` |
| `homepage`    | A URL to the website of the module                                                                   |
| `authors`     | Author information as `name`, `email`, `homepage`, `role`                                            |
| `licence`     | Licence identifier See (https://spdx.org/licenses/) or use `proprietary`                             |

**Example:**

```json
{
  "id": "example",
  "version": "1.0",
  "name": "My Example Module",
  "description": "My testing module.",
  "humhub": {
    "minVersion": "1.2"
  },
  "keywords": [
    "my",
    "cool",
    "module"
  ],
  "screenshots": [
    "assets/screen_1.jpg"
  ],
  "homepage": "https://www.example.com",
  "authors": [
    {
      "name": "Tom Coder",
      "email": "tc@example.com",
      "role": "Developer"
    },
    {
      "name": "Sarah Mustermann",
      "email": "sm@example.com",
      "homepage": "https://example.com",
      "role": "Translator"
    }
  ],
  "licence": "AGPL-3.0-or-later"
}
```

Align the `minVersion` of your module when using new features and test your modules on all supported versions. In case you are not sure about the `minVersion` use the version you are testing with or the latest stable HumHub version.

### `Module.php`

The module class of a module may contain basic install/uninstall functionality as well as module class level configuration. See chapter [Module Class](module-base-class.md) for an introduction of the base module class.

### Documentation

The documentation files of a module must be located in the module's `docs` folder.

The following table lists files which can be added in order to provide module documentation for the marketplace. Note, the
**required** field only applies to the marketplace modules and is not required for private modules.

| File            | Required | Description                                                                               |
|-----------------|----------|-------------------------------------------------------------------------------------------|
| README.md       | Yes      | A description and overview of the features                                                |
| CHANGELOG.md    | Yes      | A file which contains a curated, chronologically ordered list of changes for each version |
| MANUAL.md       | No       | Information on how to use this module                                                     |
| INSTALLATION.md | No       | Additional installation information                                                       |
| LICENCE.md      | No       | Licencing information including the licence                                               |
| DEVELOPER.md    | No       | Additional information for developers                                                     |

### Extended module structure example

The following table describes other common module directories used for more specific use cases:

| Directory       | Description                                                                       |    
|-----------------|-----------------------------------------------------------------------------------|
| `activities`    | [Activity](concept-activities.md) classes                                                 | 
| `assets`        | Asset Bundles                                                                     |
| `components`    | [Components](https://www.yiiframework.com/doc/guide/2.0/en/concept-components)    |
| `controllers`   | Web or Console controller                                                         |
| `live`          | HumHub live related classes used for live frontend updates                        |
| `jobs`          | Asynchronous jobs (queue)                                                         |
| `messages`      | Translation message files                                                         |
| `migrations`    | Database migration files                                                          |
| `helpers`       | Helper and utility classes                                                        |
| `notifications` | Module notifications                                                              |
| `permissions`   | Module [permissions](concept-permissions.md)                                              |
| `resources`     | Assets as scripts, style sheets, images                                           |
| `tests`         | Module [tests](intro-testing.md)                                                        |
| `views`         | [View](https://www.yiiframework.com/doc/guide/2.0/en/structure-views) files       |
| `widgets`       | [Widget](https://www.yiiframework.com/doc/guide/2.0/en/structure-widgets) classes |
| `Events.php`    | Event handlers                                                                    |

### Module Icon

Each module should also provide an icon image.

The icon must be provided in PNG format, squared and with a minimum size of 128x128 pixels.

By default, the image must be stored as `module_image.png` in your module's ressource directory
(see [`Module::$resourcesPath`](modules-base-class#resourcespath)), hence defaulting to `assets/module_image.png`. You can also override the `getImage()` method of your module, if you need to return a different URL.

## Module Lifecycle

### Install a Module

A module is considered as installed once it resides in one of the [module autoloader paths](intro-environment.md#module-loader-path). By default modules from [the marketplace]([url](https://marketplace.humhub.com/)) reside in `@humhub/protected/modules`. Custom modules should be installed by adding them manually to an autoload path such as `@app/custom-modules` or by loading them from the marketplace.

You can add additional module paths by means of the `moduleAutoloadPaths` parameter. Please see the [Development Environment Section](intro-environment.md#module-loader-path) for more information.

### Enabled a Module

In order to use a module, you'll have to enable it first. This can be achieved by:

- Administration Backend `Administration -> Modules`
- Console command `php yii module/enable`

Enabling a module will automatically run the modules [database migrations](concept-models.md#initial-migration)
and add an entry to the `modules_enabled` table.

The `ModuleManager` responsible for enabling modules will trigger the following events right before and after enabling a module:

- `ModuleManager::EVENT_BEFORE_MODULE_ENABLE`
- `ModuleManager::EVENT_AFTER_MODULE_ENABLE`

[ContentContainerModules](module-base-class.md#contentcontainermodule) also have to be enabled within a space or user profile within the container's module management section.

### Module Bootstrap

Every [request](https://www.yiiframework.com/doc/guide/2.0/en/runtime-overview) during the application bootstrap phase, the `humhub\components\bootstrap\ModuleAutoLoader` will search for all [enabled](#enabled-a-module)
modules within the [module autoload paths](intro-environment.md#module-loader-path) and register configured [module event listeners](concept-events.md)
defined in the modules `config.php`.

### Disable Module

Disabling a module will usually drop all related module data from the database and will detach the module from the [bootstrap](#module-bootstrap) process.

Modules can be disabled by means of

- Administration Backend `Administration -> Modules`
- Console command `php yii module/disable`

The `ModuleManager` responsible for disabling modules will trigger the following events right before and after enabling a module:

- `ModuleManager::EVENT_BEFORE_MODULE_DISABLE`
- `ModuleManager::EVENT_AFTER_MODULE_DISABLE`

See [Module::disable()](module-base-class.md#disable) and [ContentContainerModule::disable()](module-base-class.md#disable-1)
for more information about how to implement custom disable logic.

### Uninstall Module

Uninstalling a module means removing it from the autoload path.

You should never delete an enabled module folder manually without disabling it first.

# Module Development

A HumHub module is a [Yii 2 module](https://www.yiiframework.com/doc/guide/2.0/en/structure-modules) with a small amount of HumHub-specific metadata and conventions on top. Most of what HumHub adds — content containers, streams, notifications, permissions — is opt-in: a basic module needs only three files.

Familiarity with Yii 2 is assumed: [application structure](https://www.yiiframework.com/doc/guide/2.0/en/structure-overview), [controllers](https://www.yiiframework.com/doc/guide/2.0/en/structure-controllers), [models](https://www.yiiframework.com/doc/guide/2.0/en/structure-models), [views](https://www.yiiframework.com/doc/guide/2.0/en/structure-views), [assets](https://www.yiiframework.com/doc/guide/2.0/en/structure-assets).

Follow the [coding standards](intro-coding-standards.md) and keep an eye on [`MIGRATE-DEV.md`](https://github.com/humhub/humhub/blob/develop/MIGRATE-DEV.md) for breaking changes between core versions.

## Before you start

Decide which HumHub subsystems your module touches:

- Should it be [enabled per space or per user](module-base-class.md#contentcontainermodule)?
- Does it produce [content](concept-content.md) or [stream entries](concept-stream.md)?
- Does it add [sidebar snippets](ui-snippets.md), [menus](ui-menus.md), or [widgets](ui-widgets.md)?
- Does it need its own [permissions](concept-permissions.md) — including [guest access](concept-permissions.md#guest-access)?
- Does it create [notifications](concept-notifications.md) or [activities](concept-activities.md)?

Other topics:

- [Settings storage](concept-settings.md) — per-module and per-container settings
- [Event handlers](module-event-handler.md) — hook into core or other modules
- [Models & migrations](concept-models.md)
- [File handling](concept-files.md)
- [Live UI updates](concept-live.md)
- [Translations](concept-i18n.md)
- [Testing](intro-testing.md)
- [Security](advanced-security.md)
- [Embedded themes](https://docs.humhub.org/docs/theme/module)

Browse existing modules for working patterns — [github.com/humhub](https://github.com/humhub) and [github.com/humhub-contrib](https://github.com/humhub-contrib).

## Module skeleton

The fastest way to start is the [devtools](https://github.com/humhub/humhub-modules-devtools) module — it bundles a [Gii](https://www.yiiframework.com/doc/guide/2.0/en/start-gii)-based generator for module skeletons. Drop the generated directory into a [module autoload path](intro-environment.md#module-loader-path) and it shows up under *Administration → Modules*.

Alternatively, fork the [example-basic](https://github.com/humhub/example-basic) template.

## Minimal layout

```
my-module/
├── config.php       module config (id, namespace, class, events)
├── module.json      marketplace metadata
└── Module.php       module class
```

### `config.php`

Defines the module ID, class, event handlers and URL rules. The file is *loaded once and cached* — do not execute dynamic code in it.

| Attribute              | Required | Description                                                                                                |
|------------------------|----------|------------------------------------------------------------------------------------------------------------|
| `id`                   | yes      | Unique module ID — must not clash with [core](intro-overview.md#core-modules) or marketplace modules       |
| `class`                | yes      | Fully-qualified class name of the module class                                                             |
| `namespace`            | yes      | Module namespace                                                                                           |
| `events`               | no       | Event handlers — see [module event handler](module-event-handler.md)                                       |
| `urlManagerRules`      | no       | [URL Manager rules](https://www.yiiframework.com/doc/guide/2.0/en/runtime-routing#creating-rules)          |
| `modules`              | no       | Submodules                                                                                                 |
| `consoleControllerMap` | no       | Console controllers — see Yii's [`controllerMap`](https://www.yiiframework.com/doc/api/2.0/yii-base-module#$controllerMap-detail) |

Example:

```php
// @example/config.php
use humhub\widgets\TopMenu;

return [
    'id' => 'example',
    'class' => 'johndoe\example\Module',
    'namespace' => 'johndoe\example',
    'events' => [
        [
            'class' => TopMenu::class,
            'event' => TopMenu::EVENT_INIT,
            'callback' => ['johndoe\example\Events', 'onTopMenuInit'],
        ],
    ],
    'consoleControllerMap' => [
        'example' => 'johndoe\example\console\ExampleController',
    ],
];
```

### `module.json`

Marketplace metadata.

| Field         | Required | Description                                                                |
|---------------|----------|----------------------------------------------------------------------------|
| `id`          | yes      | Module ID                                                                  |
| `version`     | yes      | `X.Y.Z` semver — only bumped on release                                    |
| `name`        | yes      | Display name                                                               |
| `description` | yes      | One-line description                                                       |
| `humhub`      | no       | `minVersion` and `maxVersion` core compatibility                           |
| `keywords`    | no       | Array of keywords for marketplace search                                   |
| `screenshots` | no       | Screenshot file names, relative to [`Module::$resourcesPath`](module-base-class.md#resourcespath) |
| `homepage`    | no       | Module homepage URL                                                        |
| `authors`     | no       | `[{name, email, homepage, role}]`                                          |
| `licence`     | no       | [SPDX](https://spdx.org/licenses/) identifier, or `proprietary`            |

Example:

```json
{
    "id": "example",
    "version": "1.0.0",
    "name": "My Example Module",
    "description": "My testing module.",
    "humhub": {
        "minVersion": "1.16"
    },
    "keywords": ["my", "cool", "module"],
    "screenshots": ["assets/screen_1.jpg"],
    "homepage": "https://www.example.com",
    "authors": [
        {
            "name": "Tom Coder",
            "email": "tc@example.com",
            "role": "Developer"
        }
    ],
    "licence": "AGPL-3.0-or-later"
}
```

Bump `minVersion` whenever you start relying on a feature that arrived in a newer core version, and test against the supported range. When unsure, use the version you're developing against.

### `Module.php`

The module class hosts install/uninstall logic and module-level configuration. See [Module Class](module-base-class.md) for details.

## Documentation files

A module's `docs/` directory carries its documentation. The marketplace surfaces these files; private modules can ship any subset.

| File            | Required for marketplace | Description                                                              |
|-----------------|--------------------------|--------------------------------------------------------------------------|
| `README.md`     | yes                      | High-level description and feature overview                              |
| `CHANGELOG.md`  | yes                      | Versioned change list, newest on top                                     |
| `MANUAL.md`     | no                       | End-user documentation                                                   |
| `INSTALLATION.md` | no                     | Installation notes beyond the marketplace default                        |
| `LICENCE.md`    | no                       | Licence text                                                             |
| `DEVELOPER.md`  | no                       | Developer-facing notes                                                   |

## Extended layout

Common directories beyond the minimal three files:

| Directory        | What goes there                                                              |
|------------------|------------------------------------------------------------------------------|
| `activities/`    | [Activity](concept-activities.md) classes                                    |
| `assets/`        | Asset bundles                                                                |
| `components/`    | Yii [components](https://www.yiiframework.com/doc/guide/2.0/en/concept-components) |
| `controllers/`   | Web and console controllers                                                  |
| `live/`          | [Live](concept-live.md) event classes                                        |
| `jobs/`          | Queue jobs                                                                   |
| `messages/`      | Translation message files                                                    |
| `migrations/`    | Database migrations                                                          |
| `helpers/`       | Utility classes                                                              |
| `notifications/` | Notification classes                                                         |
| `permissions/`   | [Permission](concept-permissions.md) classes                                 |
| `resources/`     | Static scripts, stylesheets, images                                          |
| `tests/`         | [Tests](intro-testing.md)                                                    |
| `views/`         | View files                                                                   |
| `widgets/`       | Widget classes                                                               |
| `Events.php`     | Static event handlers                                                        |

## Module icon

Each module should ship a `module_image.png` — square, at least 128×128 px — in the [`resourcesPath`](module-base-class.md#resourcespath) (defaults to `assets/`). Override `Module::getImage()` to point elsewhere.

## Lifecycle

Installing, enabling, disabling and uninstalling are covered in [module lifecycle](module-lifecycle.md).

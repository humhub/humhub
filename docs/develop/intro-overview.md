# Overview

HumHub is a modular social-network platform built on the [Yii 2.0 PHP Framework](https://www.yiiframework.com/doc/guide/2.0/en/). Developers extend it by writing modules — most things you can do in the core can be done in a module.

Languages used throughout the platform:

- PHP (server)
- JavaScript (client)
- MySQL / MariaDB (storage)
- HTML, CSS / Sass (views)

The frontend stack is [jQuery](https://jquery.com/), [Bootstrap 5.3](https://getbootstrap.com/docs/5.3), [Sass](https://sass-lang.com/) and [Font Awesome 4.7](https://fontawesome.com/v4.7.0/).

Familiarity with Yii 2 — controllers, models, views, asset bundles — is assumed. See the [Definitive Guide to Yii 2.0](https://www.yiiframework.com/doc/guide/2.0/en/).

## Core components

The core extends a number of Yii base classes under `humhub\components\`:

- `ActiveRecord`, `Migration`, `SettingActiveRecord` — persistence
- `Application`, `Module`, `ModuleManager`, `ModuleEvent` — application & module system
- `Controller`, `Request`, `Response`, `UrlManager` — request lifecycle
- `Event`, `SocialActivity` — events & activities
- `Widget`, `View`, `ViewMeta` — view layer
- `Theme`, `ThemeVariables`, `ThemeViews` — theming
- `SettingsManager` — settings storage

## Core modules

The core ships a fixed set of modules under `protected/humhub/modules/`. Custom modules use them as building blocks rather than duplicating their functionality.

| Module         | Description                                                             |
|----------------|-------------------------------------------------------------------------|
| `activity`     | Social-network [activities](concept-activities.md)                      |
| `admin`        | Administration backend                                                  |
| `comment`      | Content add-on for commenting                                           |
| `content`      | Base module for all content types (Post, Wiki, …)                       |
| `dashboard`    | Dashboard overview                                                      |
| `directory`    | Directory of users, spaces, groups                                      |
| `file`         | Uploaded-file management                                                |
| `friendship`   | User friendship relations                                               |
| `installer`    | Platform installer                                                      |
| `ldap`         | LDAP / Active Directory integration                                     |
| `like`         | Content add-on for likes                                                |
| `live`         | Live updates pushed to the frontend                                     |
| `marketplace`  | Marketplace interface                                                   |
| `notification` | Notifications across web, mail and other channels                       |
| `post`         | Simple post content type                                                |
| `queue`        | Asynchronous job queue                                                  |
| `search`       | Search abstraction + default implementation                             |
| `space`        | Spaces (group containers for content)                                   |
| `stream`       | Content streams                                                         |
| `topic`        | Topics that categorize content                                          |
| `tour`         | First-login introduction tour                                           |
| `ui`           | Base UI components, widgets, theming                                    |
| `user`         | User accounts, authentication, [user sources](user-source.md)           |
| `web`          | Web-standard helpers                                                    |

## Application layout

The installation tree. The `humhub` directory is referenced as `web-root` throughout the docs.

```
humhub
├── assets         published asset bundles (scripts, stylesheets)
├── protected
│   ├── config     user configuration files
│   ├── humhub     core source
│   ├── modules    default search path for external modules
│   ├── runtime    cache, search index, logs
│   └── vendor     third-party libraries (Composer)
├── static         static asset files (production assets, core JS / SCSS)
├── themes
└── uploads        file uploads, profile images
```

The `protected/` directory MUST NOT be web-accessible in production.

See the [Yii 2 Application Structure](https://www.yiiframework.com/doc/guide/2.0/en/structure-overview) guide for the general layout.

# Overview

## The HumHub platform

HumHub provides a powerful modular platform based on the [Yii2 Framework](http://www.yiiframework.com).
The modular nature of HumHub allows developers to add new features or change the behavior of existing core 
features by means of custom modules.

Languages used throughout the platform are: 

- **PHP**
- **JavaScript**
- **SQL (MySQL/MariaDB)**
- **HTML**
- **CSS/Sass**

HumHub is based on the [Model-View-Controller (MVC)](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) 
pattern and uses frontend frameworks such as: 

- [jQuery](https://jquery.com/)
- [Bootstrap 5.3](https://getbootstrap.com/docs/5.3)
- [Sass](https://sass-lang.com/)
- [Fontawesome](https://fontawesome.com/v4.7.0/).

In this guide you will find all the necessary information in order to customize your HumHub installation and implement your own modules.
Since HumHub is based on the [Yii 2.0 PHP Framework](http://www.yiiframework.com/) make sure you're also familiar with the basic concepts of Yii2:

- [The Definitive Guide to Yii 2.0](http://www.yiiframework.com/doc-2.0/guide-index.html) 

## Core modules and components

The HumHub core consists of a set of core modules, components, widgets, helpers and views, which can be used within your
custom modules and are described in more detail within the [Module Development](module-development.md) section.

HumHub extends several Yii components such as:

 - `humhub\components\ActiveRecord`
 - `humhub\components\Application`
 - `humhub\components\AssetManager`
 - `humhub\components\Controller`
 - `humhub\components\Event`
 - `humhub\components\ActiveRecord`
 - `humhub\components\Migration`
 - `humhub\components\Module`
 - `humhub\components\Request`
 - `humhub\components\Response`
 - `humhub\components\Theme`
 - `humhub\components\UrlManager`
 - `humhub\components\View`
 - `humhub\components\Widget`
 - and more...

and consists of the following core modules:

| Module         | Description                                                 |    
|----------------|-------------------------------------------------------------|
| `activity`     | Social network [activities](concept-activities.md)                  | 
| `admin`        | Administration backend                                      |
| `comment`      | Content add-on for commenting                               |
| `content`      | Base module for all content types (Post,Wiki,...)           |
| `dashboard`    | HumHub Dashboard overview                                   |
| `directory`    | HumHub Directory platform overview (User/Spaces/Groups)     |
| `file`         | Base file module for managing uploaded files                |
| `friendship`   | Enables user friendship relations                           |
| `installer`    | HumHub platform installer                                   |
| `like`         | Content add-on for likes                                    |
| `live`         | Enables live updates in the frontend                        |
| `marketplace`  | Marketplace interface                                       |
| `notification` | Enables notification over different targets                 |
| `post`         | Simple post content type                                    |
| `queue`        | Queue module for asynchronous jobs                          |
| `search`       | HumHub search abstraction + default implementation          |
| `space`        | User Spaces                                                 |
| `stream`       | Content Streams                                             |
| `topic`        | Content topics used to categorize content entries           |
| `tour`         | Introduction tour (user-guide)                              |
| `ui`           | Base user interface components like widgets and theme logic |
| `user`         | HumHub user and authentication                              |
| `web`          | Web standard related classes                                |
 
## Application structure

The following structure lists the main directories of a HumHub installation, whereas the `humhub` directory will be referred as
 `web-root` throughout this guide.

```
humhub
├── assets
├── protected
│   ├── config
│   ├── humhub
│   ├── modules
│   ├── runtime
│   └── vendor
├── static
├── themes
└── uploads
```

- `assets` - Contains published assets as scripts and stylesheets managed by [AssetBundles](https://www.yiiframework.com/doc/guide/2.0/en/structure-assets#asset-bundles).
- `protected` - Contains files as core and module sources and configuration files. The access to this directory needs to be protected 
in an production environment.
  - `config` - Contains user configuration files.
  - `humhub` - Contains all HumHub core files'
  - `modules` - This is the default directory used for searching for external modules.
  - `runtime` - Contains runtime related files as **cache**, **search index** and **logs**
  - `vendor` - Contains third party libraries
- `static` - Static asset files as production assets, core javascript/scss files etc.
- `uploads` - File uploads, profile images etc.

See [Yii2 - Application Structure](https://www.yiiframework.com/doc/guide/2.0/en/structure-overview) for an in depth description
of a Yii2 application.

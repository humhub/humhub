Overview
========

HumHub provides a powerful modular platform based on the [Yii2 Framework](http://www.yiiframework.com).
The modular nature of the HumHub platform allows you to add new features or change existing core features by means of
custom modules.

Other languages used throughout the platform, besides **PHP**, are **JavaScript**, **HTML**, **SQL** and **CSS/Less**. 
HumHub is based on the [Model-View-Controller (MVC)](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) 
pattern and uses frontend technologies such as [jQuery](https://jquery.com/), [Bootstrap](http://getbootstrap.com/) and [Less](http://lesscss.org/).

In this guide you will find all the necessary information to customize your HumHub installation and implement your own modules.

As HumHub is based on the [Yii 2.0 PHP Framework](http://www.yiiframework.com/) make sure you're also familiar with the basic concepts of this framework:

- [The Definitive Guide to Yii 2.0](http://www.yiiframework.com/doc-2.0/guide-index.html) 

## HumHub Core

![Application Layers](images/appLayer.svg)

The HumHub core consists of a set of core components, modules, widgets, helpers and views.
HumHub extends several Yii base components such as:

 - [[humhub\components\ActiveRecord|ActiveRecord]]
 - [[humhub\components\Application|Application]]
 - [[humhub\components\Controller|Controller]]
 - [[humhub\components\Migration|Migration]]
 - [[humhub\components\Module|Module]]
 - [[humhub\components\ModuleManager|ModuleManager]]
 - [[humhub\components\Request|Request]]
 - [[humhub\components\Theme|Theme]]
 - [[humhub\components\User|User]]
 - [[humhub\components\View|View]]
 - [[humhub\components\Widget|Widget]]
 - and more...

and consists of the following core modules:

 - **activity:** Assambles social network activities
 - **admin:**  Responsible for admin/configuration related issues
 - **comment:**  Content addon for commenting
 - **content:**  Base module for all content types (Post,Wiki,...) 
 - **dashboard:**  Dashboard related functionality
 - **directory:**  User/Space/Group directory
 - **file:**  Basic file module for accessing the filesystem
 - **friendship:**  User friendship module
 - **installer:**  HumHub installer module
 - **like:**  Content addon for likes
 - **live:**  Used for frontend live updates
 - **notification:**  User Notifications
 - **post:**  Simple user-post related functionality
 - **queue:** Queue drivers and interfaces
 - **search:**  Luceene Search Module
 - **space:**  Space related functionality
 - **stream:**  Content streams and walls
 - **topic:** Topics are used to categorize and filter content
 - **tour:**  HumHub user-guide
 - **ui:** Base ui components as widgets and theme logic
 - **user:**  Basic user module
 
### Core directories
 
```
  assets/               - contains published asset files
  protected/            - protected files as sources, modules, configuration etc.
  protected/config      - dynamic and user configuration
  protected/humhub      - humhub core directory
  protected/modules     - default directory for non core modules
  protected/runtime     - runtime files as cache, search index, logs etc.
  protected/vendor      - third party libraries loaded by composer
  static/               - static asset files as production assets core javascript/less files etc.
  themes/               - contains standalone themes (not bundled within a module)
  uploads/              - uploaded files profile images etc.
```

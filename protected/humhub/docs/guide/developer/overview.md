Overview
========

HumHub is a very powerful platform, and part of its power lies in the fact that it is very easy to build upon and expand through modules and overriding code.
HumHub is written mostly in PHP based on the Yii Framework.  

Other languages used throughout are JavaScript, HTML, SQL and CSS. 
It uses a Model-View-Controller (MVC)-like pattern for its software architecture. Additionally, it uses technologies such as Yii2, jQuery, Bootstrap, Less, etc.
In this guide, you will find all the necessary information to customize HumHub.

As HumHub is based on Yii 2.0 PHP Framework (http://www.yiiframework.com/) make sure you're also familiar with this framework.
[The Definitive Guide to Yii 2.0](http://www.yiiframework.com/doc-2.0/guide-index.html) 

Application Overview
--------------------

Humhub is based on _PHP5_ and _Yii2_ and leverages the highly modular and flexible nature of _Yii_.
Before learning about the internals of HumHub, you should be familiar with the basic concepts of
[Yii](http://www.yiiframework.com/doc-2.0/guide-README.html "Yii Guide").

![Application Layers](images/appLayer.svg)

The HumHub core contains several core modules as well as extended Yii components:

**Core Components:**

 - [[humhub\components\ActiveRecord]]
 - [[humhub\components\Application]]
 - [[humhub\components\Controller]]
 - [[humhub\components\Migration]]
 - [[humhub\components\Module]]
 - [[humhub\components\ModuleManager]]
 - [[humhub\components\Request]]
 - [[humhub\components\Theme]]
 - [[humhub\components\View]]
 - [[humhub\components\Widget]]

**Core Modules:**

 - **activity:**  User/Space activities
 - **admin:**  Responsible for admin/configuration related issues
 - **comment:**  Content addon for commenting
 - **content:**  Base module for all content types (Post,Wiki,...) 
 - **dashboard:**  Dashboard related functionality
 - **directory:**  Directory related functionality
 - **file:**  Basic file module for accessing the filesystem
 - **installer:**  HumHub installer module
 - **like:**  Content addon for likes
 - **notification:**  User Notifications
 - **post:**  Simple user-post related functionality
 - **search:**  Luceene Search Module
 - **space:**  Space related functionality
 - **tour:**  HumHub user-guide
 - **user:**  Basic user module

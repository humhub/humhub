Development Environment
=======================

This guide shows some recommended settings of your development environment.

Quick Notes
-----------

- Make sure you are using a [Git/Composer HumHub installation](git-installation.md)
- Make sure the `debug` mode is enabled (default), see [Enable Production Mode](../admin/security.md#enable-production-mode)
- Disable caching under `Administration -> Settings -> Advanced -> Caching -> None`
- Use file based mailing `Administration -> Settings -> Advanced -> E-Mail`

Since HumHub v1.3 makes heavy use of [Queues](../admin/asynchronous-tasks.md) you should configure the [Instant or Sync Queue]([Queues](../admin/asynchronous-tasks.md#sync-and-instant-queue)) 
queue in your development environment. Otherwise you'll have to execute the `queue/run` command manually in order to test `Notifications` or other queued jobs.

Git/Composer Installation
=========================

The following guide describes a git based installation of the HumHub platform. Please note that this is only recommended for
developers and testers and should not be used in production environments. 
For production environments, please follow the [Installation Guide for Administrators](../admin/installation.md).

Database Setup
-----------
Please follow the [Database Setup Section](../admin/installation.md#database-setup) of the administration installation guide.

Get HumHub
----------
 - Install [git](https://git-scm.com/)
 - Clone the git Repository:

```
git clone https://github.com/humhub/humhub.git
```

 - Install composer ([https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md))
 - Navigate to your HumHub webroot and fetch dependencies:
 
```
composer install
```

> Note: The composer update may have to be executed again after an update of your local repository by a git pull. Read more about updating ([Update Guide](../admin/updating.md))

> Note: Since HumHub 1.3 you have to build the production assets manually, please see the [Build Assets Section](build.md#build-assets) for more information.

 - Follow further instructions of the [Installation Guide](../admin/installation.md)

External Modules Directory
-----------------

Custom modules can also be located outside of the default HumHub `modules` directory by
adding a path to the `moduleAutoloadPaths` array parameter in your `protected/config/common.php` configuration. This separation can
be useful while developing custom modules.

```php
return [
    'params' => [
        'moduleAutoloadPaths' => ['/some/folder/modules'],        
    ],
]
```

Yii Debug Module
----------------

You may want to enable the [Yii's debug Module](http://www.yiiframework.com/doc-2.0/ext-debug-index.html) for detailed
request and query debugging.
 
Just add the following block to your local web configuration `protected/config/web.php`:

```php
return [
    // ...
    'bootstrap' => ['debug'],
	'modules' => [
	    // ...
	    'debug' => [
	        'class' => 'yii\debug\Module',
	        'allowedIPs' => ['127.0.0.1', '::1'],
	    ],
            // ...
	]
];
```

Developer Tools Module
-------------------

The [devtools Module](https://github.com/humhub/humhub-modules-devtools) provides some useful showcases of widgets and a Module generator based on [Gii](https://www.yiiframework.com/doc/guide/2.0/en/start-gii).

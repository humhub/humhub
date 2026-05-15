# Development Environment

## Requirements

- [MySQL](https://www.mysql.com/) or [MariaDB](https://mariadb.org/)
- A webserver of your choice e.g. [Apache](https://httpd.apache.org/) or [nginx](https://www.nginx.com/)
- [Git](https://git-scm.com/)
- [Composer](https://getcomposer.org/doc/00-intro.md)

## Git/Composer Installation

The following guide describes a git based installation of the HumHub platform. Please note that this is only recommended for
developers and testers and should not be used in production environments. For production environments, 
please follow the [Installation Guide for Administrators](https://docs.humhub.org/docs/admin/installation). 

### Database Setup

Create a MySQL/MariaDB database:

```sql
 CREATE DATABASE `humhub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Fore more infos check the [Database Setup Section](https://docs.humhub.org/docs/admin/server-setup#database).

### Clone HumHub

Clone the git repository into the `htdocs` directory of your webserver:

```console
git clone https://github.com/humhub/humhub.git
```

### Run Composer

Navigate to your HumHub `web-root` directory and fetch your composer dependencies:
 
```console
composer install
```

### Run web installer

The HumHub web installer should now be accessible e.g. under [http://localhost/humhub](http://localhost/humhub).
Please refer to the [Installation Guide](https://docs.humhub.org/docs/admin/installation) for further instructions as file permissions.

## Configuration

### Quick Notes

- Copy the contents of ``.env.example`` into the newly created ``.env`` file.
- Make sure the ``DEBUG`` mode is enabled (default), see [Enable Debug Mode](https://docs.humhub.org/docs/admin/troubleshooting#debug-mode)
- Disable caching under ``Administration > Settings > Advanced > Caching > None``
- Use file based mailing ``Administration > Settings > Advanced > E-Mail``

### Queue configuration

Since HumHub makes heavy use of [Queued Jobs](https://docs.humhub.org/docs/admin/asynchronous-tasks) you should configure the
[Instant or Sync Queue](https://docs.humhub.org/docs/admin/asynchronous-tasks#sync-and-instant-queue) for your developement environment or setup the 
[Cronjob](https://docs.humhub.org/docs/admin/asynchronous-tasks#workers--job-processing) for a production like environment. Otherwise you'll
have to run `queue/run` command manually in order to execute queued tasks as notifications and summary mail.

### Module Loader Path

The default path where HumHub searches for installed modules is ``@humhub/protected/modules``. Additional search paths can be configured in the ``.env`` file as follows:

```env
// @humhub/.env
HUMHUB_CONFIG__PARAMS__MODULE_AUTOLOAD_PATHS=['/some/folder/modules', '/some/other-folder/modules']
```
This separation should be done for custom modules which are not in [the marketplace](https://marketplace.humhub.com/)
(e.g, `@app/custom-modules`) or for your development environment in order to define a central module directory
for different test installations and prevent interference with marketplace modules.

### Yii Debug Module

You may want to enable the [Yii Debug Module](http://www.yiiframework.com/doc-2.0/ext-debug-index.html) for detailed request and query debugging. Just add the following block to your local web configuration::

in `.env`
```env
// @humhub/.env
HUMHUB_CONFIG__BOOSTRAP=['debug']
HUMHUB_CONFIG__MODULES__DEBUG='{"class":"yii\\\debug\\\Module", "allowedIPs": ["127.0.0.1", "::1"]}'
```

or in `protected/config/web.php`
```php
return [
    'bootstrap' => ['debug'],
	'modules' => [
	    'debug' => [
	        'class' => 'yii\debug\Module',
	        'allowedIPs' => ['127.0.0.1', '::1'],
	    ],
	]
];
```

## Update your installation

Git based installations can be updated manually as follows:

- Pull updates from git:
 
```console
git pull origin master
```

- Run database migrations within your HumHub `protected` directory:

```console
php yii migrate/up --includeModuleMigrations=1
```

- You may also need to also run a `composer install` after an update in order to update third party dependencies.

> ⚠️ Note that the [HumHub Updater](https://marketplace.humhub.com/module/updater) module won't work on git based installations, 
>therefore you'll have to update and migrate your HumHub manually.

## Production Mode

A git based installation won't run in production mode without [building the production assets](intro-build.md#build-production-assets)
manually. This is also required in order to run [acceptance tests](intro-testing.md#run-acceptance-tests).

## Test Environment

Please refer to the [Testing Guide](intro-testing.md#test-environment-setup) for information about setting up a test environment
and running tests on core custom modules.

## HumHub Developer Tools

The [devtools](https://github.com/humhub/humhub-modules-devtools) module provides some useful showcases 
and a module generator based on [Gii](https://www.yiiframework.com/doc/guide/2.0/en/start-gii).

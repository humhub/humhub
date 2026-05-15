# Development Environment

A git-based setup for working on the HumHub core or modules. Not for production — production installs follow the [admin installation guide](https://docs.humhub.org/docs/admin/installation).

## Requirements

- PHP — see the [admin requirements page](https://docs.humhub.org/docs/admin/requirements) for the supported version range
- [MySQL](https://www.mysql.com/) or [MariaDB](https://mariadb.org/)
- A web server — [Apache](https://httpd.apache.org/) or [nginx](https://www.nginx.com/)
- [Git](https://git-scm.com/) and [Composer](https://getcomposer.org/)

## Installation

### 1. Create the database

```sql
CREATE DATABASE `humhub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

More on database setup: [admin server-setup guide](https://docs.humhub.org/docs/admin/server-setup#database).

### 2. Clone the repository

```sh
git clone https://github.com/humhub/humhub.git
```

### 3. Install Composer dependencies

In the `web-root` (the cloned repo):

```sh
composer install
```

### 4. Run the web installer

The installer is at `http://localhost/humhub` (or wherever you pointed your web server). The [admin installation guide](https://docs.humhub.org/docs/admin/installation) covers file permissions and other server-side details.

## Configuration

Copy `.env.example` to `.env` and adjust as needed.

For a development install:

- Keep `DEBUG` mode enabled (default — [Enable Debug Mode](https://docs.humhub.org/docs/admin/troubleshooting#debug-mode))
- Disable caching under *Administration → Settings → Advanced → Caching → None*
- Use file-based mailing under *Administration → Settings → Advanced → E-Mail*

### Queue

HumHub uses [queued jobs](https://docs.humhub.org/docs/admin/asynchronous-tasks) for notifications, mails and other background work. In development, either configure the [Instant or Sync queue](https://docs.humhub.org/docs/admin/asynchronous-tasks#sync-and-instant-queue), or run jobs manually:

```sh
php yii queue/run
```

For production-like behaviour, set up the [cron worker](https://docs.humhub.org/docs/admin/asynchronous-tasks#workers--job-processing).

### Module loader path

By default modules are loaded from `@humhub/protected/modules`. Add custom paths via `.env`:

```env
HUMHUB_CONFIG__PARAMS__MODULE_AUTOLOAD_PATHS=['/some/folder/modules', '/some/other-folder/modules']
```

This is useful for keeping your own modules separate from [marketplace](https://marketplace.humhub.com/) modules, or for sharing a central module directory across multiple test installations.

### Yii Debug Toolbar

Enable the [Yii Debug Module](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide/2.0/en/README) for request and query profiling.

Via `.env`:

```env
HUMHUB_CONFIG__BOOTSTRAP=['debug']
HUMHUB_CONFIG__MODULES__DEBUG='{"class":"yii\\\\debug\\\\Module","allowedIPs":["127.0.0.1","::1"]}'
```

Or in `protected/config/web.php`:

```php
return [
    'bootstrap' => ['debug'],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            'allowedIPs' => ['127.0.0.1', '::1'],
        ],
    ],
];
```

## Updating

For a git-based install:

```sh
git pull origin master
composer install
php yii migrate/up --includeModuleMigrations=1
```

The [HumHub Updater](https://marketplace.humhub.com/module/updater) module does **not** work on git-based installations.

## Production mode

A git checkout won't run in production mode without [building production assets](intro-build.md#build-production-assets). The build is also required to run [acceptance tests](intro-testing.md#run-acceptance-tests).

## Test environment

See the [testing guide](intro-testing.md#test-environment-setup) for setting up the test environment.

## Developer tools module

The [devtools module](https://github.com/humhub/humhub-modules-devtools) provides example components and a module skeleton generator based on [Gii](https://www.yiiframework.com/doc/guide/2.0/en/start-gii).

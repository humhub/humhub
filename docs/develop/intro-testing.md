# Testing

HumHub uses [Codeception](https://codeception.com/) (^5.0) for unit, functional and acceptance tests. The Codeception binary lives at `protected/vendor/bin/codecept` — Composer manages it, no global install needed.

## Test types

| Type           | Browser? | JS? | What it's good for                                                         |
|----------------|----------|-----|----------------------------------------------------------------------------|
| **Unit**       | no       | no  | Individual classes / services. Fastest. Default for component logic.       |
| **Functional** | no       | no  | Controllers, forms, access rules. Direct access to the Yii app context.    |
| **Acceptance** | yes      | yes | End-to-end through Chrome via Selenium. Slowest. Use for UI / JS coverage. |

See [Codeception's introduction](https://codeception.com/docs/01-Introduction) and the [Yii testing guide](https://www.yiiframework.com/doc/guide/2.0/en/test-overview).

## Setting up the test environment

### 1. Test database

```sql
CREATE DATABASE `humhub_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Database access

Configure the test connection in `protected/humhub/tests/config/common.php`:

```php
'components' => [
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=humhub_test',
        'username' => 'myUser',
        'password' => 'myPassword',
        'charset' => 'utf8',
    ],
]
```

### 3. Run migrations

```sh
cd protected/humhub/tests/codeception/bin
php yii migrate/up --includeModuleMigrations=1 --interactive=0
```

Repeat this whenever core or a module ships new migrations.

### 4. Install test environment

```sh
php yii installer/auto
```

### 5. `HUMHUB_PATH` (non-core modules only)

For module repositories outside the core tree, set `humhub_root` in `<module>/tests/config/test.php`:

```php
return [
    'humhub_root' => '/path/to/humhub',
];
```

## Test configuration

Suite configs are merged in this order — later entries override earlier ones:

1. `protected/humhub/tests/config/<suite>.php` (e.g. `functional.php`)
2. `<module>/tests/config/common.php`
3. `<module>/tests/config/<suite>.php`
4. `<module>/tests/config/env/<env>/common.php` *(if exists)*
5. `<module>/tests/config/env/<env>/<suite>.php` *(if exists)*

### Per-environment configuration

Run against a specific HumHub checkout via Codeception's `--env`:

```sh
codecept run functional --env master
```

Provide an env-specific config — e.g. `<module>/tests/config/env/master/test.php`:

```php
return [
    'humhub_root' => '/path/to/humhub-master',
];
```

A module's `test.php` should list every non-core module the tests rely on under `modules`.

## Building tests

After adding or removing modules, rebuild Codeception's actor classes:

```sh
cd protected/humhub/tests
codecept build
```

Or pass `-b` to a `grunt test` run.

## Running tests

### All core tests

```sh
cd protected/humhub/tests
codecept run
```

Or via Grunt:

```sh
grunt test
```

### A single core module

```sh
cd protected/humhub/modules/user/tests
codecept run
```

Or:

```sh
grunt test --module=user
```

### A single suite, file or method

```sh
codecept run unit
codecept run unit FollowTest
codecept run unit FollowTest:testFollowUser
```

Equivalent Grunt invocations:

```sh
grunt test --module=user --suite=unit
grunt test --module=user --path=unit/FollowTest
grunt test --module=user --path=unit/FollowTest:testFollowUser
```

`grunt test` currently only handles core-module tests — for non-core modules, run `codecept` directly.

### Non-core modules

In your module's `tests/config/test.php`:

```php
return [
    'modules' => ['myModuleId'],
];
```

If the module sits outside `protected/humhub/modules/`, also extend the autoload path in `tests/config/common.php`:

```php
return [
    'params' => [
        'moduleAutoloadPaths' => ['/path/to/my/modules'],
    ],
];
```

## Acceptance tests

Acceptance tests drive Chrome through Selenium / `chromedriver`.

### 1. Install ChromeDriver + Selenium

Download a [chromedriver](https://chromedriver.chromium.org/downloads) matching your Chrome version and a [Selenium server](https://www.selenium.dev/downloads/).

Start Selenium with the driver path:

```sh
java -Dwebdriver.chrome.driver=/path/to/chromedriver -jar selenium-server.jar standalone
```

### 2. Start the test web server

The test suite hits `http://localhost:8080/`.

```sh
grunt test-server
```

Or directly:

```sh
php -S 127.0.0.1:8080 index-test.php
```

If another web server already exposes HumHub on port 8080, you can skip this — but you may need to adjust `test-entry-url` in `codeception.yml` and the `url` in `acceptance.suite.yml`.

### 3. Build production assets

Acceptance tests run against the production asset bundle. See [Build → Build production assets](intro-build.md#build-production-assets).

### 4. Run

```sh
codecept run acceptance
```

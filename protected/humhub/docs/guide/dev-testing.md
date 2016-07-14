Testing
====================

## Setup your testing environment

1. Install codeception

```
composer global require "codeception/codeception=2.0.*" "codeception/specify=*" "codeception/verify=*"
```

2. Create test Database:

```
CREATE DATABASE `humhub_test` CHARACTER SET utf8 COLLATE utf8_general_ci;
```

3. Align your database settings in protected/humhub/tests/config/common.php:

```
return [
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=humhub_test',
            'username' => '<username>',
            'password' => '<password>',
            'charset' => 'utf8',
        ], 
    ]
];
```

3. Migrate Up:

```
cd protected/humhub/tests/codeception/bin
php yii migrate/up --includeModuleMigrations=1 --interactive=0
php yii installer/auto
```

4. Run Tests (core):

```
cd protected/humhub/tests/
codecept run
```

## Run test suites

To run a specific test suite execute:

```
codecept run functional

or

codecept run acceptance

or

codecept run unit
```

The corresponding test files for a suite should reside under module_root/tests/codeception/<suite>

### Test configuration

The settings of your default configuration files in your `humhub_root/protected/humhub/tests/config` directory can be overwritten for each module
within the corresponding `module_root/tests/config` files.

The following configuration files should can be used to overwrite the defaults:

 - The initial test configuration _test.php_ is used to set general test settings as the path to your humhub_root.
 - The configuration of _common.php_ is used for all suites and can be overwritten by settings in a suite configuation.
 - Suite specific test configurations (e.g. functional.php) are used to configure humhub for suite tests.

The configurations for a suite will be merged in the following order:

 - humhub_root/protected/humhub/tests/config/functional.php
 - module_root/tests/config/common.php
 - module_root/tests/config/functional.php
 - module_root/tests/config/env/<env>/common.php (if exists)
 - module_root/tests/config/env/<env>/functional.php (if exists)

#### Environments

For running a test for a specific environment you'll have to set te `--env` argument for your testrun.

Example for running all functional tests of a tasks module in a master environment:

1. Create a file `tasks/tests/config/env/master/test.php` with the following content:

```
return [
    'humhub_root' => '/pathToMasterBranch'
];
```

2. If needed set further humhub settings in `tasks/tests/config/env/master/funtional.php`
3. Run `codecept run functional --env master`

>Note: Your modules test.php configuration file should always set an 'modules' array with all non core modules needed for test execution.

>Note: You can specify multiple --env arguments in codeception, for module tests the first --env argument must contain the environment of your
humhub_root settings you want to use for this test run.

## Run tests for modules

To run non core module tests you have to edit at least the test config file under `<yourmodule>/tests/config/test.php`.
If your modules reside outside of the /protected/humhub/modules directory you'll have to set the following configurations:

In your `<yourmodule>/tests/config/test.php`:

```
return [
    // This should contain the root path of your humhub branch you want to test against
    'humhub_root' => '/pathToHumHub',
    // This will enable all provided modules for the testenvironment
    'modules' => ['myModuleId']
];
```

In your `<yourmodule>/tests/config/common.php`:

```
return [
    'params' => [
        // This is a humhub configuration to include (but not enable) all modules within the given path
        'moduleAutoloadPaths' => ['/pathToMyModules']
    ]
];
```


[https://github.com/yiisoft/yii2-app-advanced/blob/master/tests/README.md]

#### PhantomJs

```
 protected/vendor/bin/phantomjs --webdriver=4444
```
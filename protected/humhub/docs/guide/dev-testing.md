Testing (since v1.2)
====================

## Testenvironment setup

1. Install codeception

composer global require "codeception/codeception=2.0.*" "codeception/specify=*" "codeception/verify=*"

2. Create test Database:

CREATE DATABASE `humhub_test` CHARACTER SET utf8 COLLATE utf8_general_ci;

3. Configure database access

Configure your database auth in @humhub/tests/config/common.php:

´´´
...
'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=humhub_test',
            'username' => 'myUser',
            'password' => 'myPassword',
            'charset' => 'utf8',
        ], 
        ...
]
...
´´´

4. Migrate Up:

```
cd protected/humhub/tests/codeception/bin
php yii migrate/up --includeModuleMigrations=1 --interactive=0
php yii installer/auto
``` 

5. Install test environment:

´´´
 cd protected/humhub/tests/codeception/bin
 php yii migrate/up --includeModuleMigrations=1 --interactive=0
 php yii installer/auto
´´´

6. Set HUMHUB_PATH system variable

You should set the HUMHUB_PATH environment which should point to your HumHub root directory you want to use for testing.
This is only required for non core module tests and can also be set in your modules test configuration ´/tests/config/test.php´:

´´´
return [
    'humhub_root' => '/path/to/my/humhub/root',
];
´´´

> Note: The test environment only works with HumHub v1.2 or later.

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

## Run Tests:

### Run all core tests:

´´´
cd protected/humhub/tests/
codecept run
´´´

### Run core module test

´´´
cd myModule/tests
codecept run unit
´´´

### Run non core module tests

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

### Run single test

´´´
codecept run codeception/acceptance/TestCest:testFunction
´´´

### Run acceptance tests
#### with phantomjs

- Run phantomjs server (is installed with composer update)

cd protected/vendor/bin
phantomjs --webdriver=44444

#### with chrome driver (selenium)

- Download chromedriver.exe and selenium standalone server jar and copy them in the same directory.

Start selenium:

´´´
java -Dwebdriver.chrome.driver=chromedriver.exe -jar selenium-server-standalone-2.53.0.jar
´´´

Start test server:

´´´
cd /myhumHubInstallation
php -S localhost:8080
´´´

run with chrome environment
codecept run acceptance --env chrome


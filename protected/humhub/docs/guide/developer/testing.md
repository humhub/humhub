Testing (since v1.2)
====================

## Test Environment Setup

-  Install codeception ([http://codeception.com/install](http://codeception.com/install))
-  Composer require codeception:

```
composer global require "codeception/codeception=2.0.*" "codeception/specify=*" "codeception/verify=*"
```

- Create test Database:

```
CREATE DATABASE `humhub_test` CHARACTER SET utf8 COLLATE utf8_general_ci;
```

- Configure database access:

Configure the database connection for your test environment in `@humhub/tests/config/common.php`:


 
    'components' => [
	    'db' => [
	    'dsn' => 'mysql:host=localhost;dbname=humhub_test',
		    'username' => 'myUser',
		    'password' => 'myPassword',
		    'charset' => 'utf8',
    	], 
    ]


- Run Database Migrations:
    
    
```cd protected/humhub/tests/codeception/bin```

```php yii migrate/up --includeModuleMigrations=1 --interactive=0```

```php yii installer/auto```

>Note: You'll have to run the migrations for your test environment manually in order to keep your test environment up to date.

- Install test environment:

```cd protected/humhub/tests/codeception/bin```

```php yii migrate/up --includeModuleMigrations=1 --interactive=0```

```php yii installer/auto```

- Set `HUMHUB_PATH` system variable

The `HUMHUB_PATH` is used by your test environment to determine the humhub root path.
This is only required for non core module tests and can also be set in your modules test configuration `/tests/config/test.php`:

    return [
    	'humhub_root' => '/path/to/my/humhub/root',
    ];


## Test configuration

The settings of your default configuration files in your `humhub_root/protected/humhub/tests/config` directory can be overwritten for each module
within the corresponding `<module_root>/tests/config` files.

The following configuration files can be used to overwrite the test configuration:

 - The initial test configuration `test.php` is used to set general test configurations as the path to your `humhub_root` or used fixtures.
 - The configuration of `common.php` is used for all suites and can be overwritten by settings in a suite configuation.
 - Suite specific test configurations (e.g. functional.php) are used to configure humhub for suite tests.

The configurations for a suite will be merged in the following order:

 - @humhub/protected/humhub/tests/config/functional.php
 - @myModule/tests/config/common.php
 - @myModule/tests/config/functional.php
 - @myModule/tests/config/env/myenv/common.php (if exists)
 - @myModule/tests/config/env/myenv/functional.php (if exists) 

### Environments

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

```
codecept run codeception/acceptance/TestCest:testFunction
```

### Run acceptance tests
#### with phantomjs

- Run phantomjs server (is installed with composer update)

```cd protected/vendor/bin```

```phantomjs --webdriver=44444```

#### with chrome driver (selenium)

- Download chromedriver.exe and selenium standalone server jar and copy them in the same directory.

Start selenium:

```
java -Dwebdriver.chrome.driver=chromedriver.exe -jar selenium-server-standalone-2.53.0.jar
```

Start test server:

```cd /myhumHubInstallation```

```php -S localhost:8080```

run with chrome environment:

```codecept run acceptance --env chrome```


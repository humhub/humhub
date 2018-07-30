Testing
=======

## Getting Started

Testing in Humhub/Yii extends the paradigm of unit testing by functional and acceptance tests. 

**Acceptance tests** simulate actual users actions on a browser. Therefore acceptance tests are the most bulletproof way
to make sure your code works as expected. By means of acceptance tests you do not only test your backend code but also
your javascript frontend potentially on different browsers.  The downside of acceptance tests is the long execution time, and a more complex implementation.
Implement acceptance tests for general UI tests, ideally access all views and test Javascript based views.

**Functional tests** are similar to acceptance tests, with the difference that functional tests are not operated on an actual browser
and do not execute any javascript. Functional tests allow easy testing of customized http requests and also allow direct access of
application logic. This can be handy if you require some sort of state for your tests like specific settings etc. 
Write functional tests in order to test your controllers, forms and controller access for different configurations settings.

**Unit tests** are ideal for [white box testing](https://en.wikipedia.org/wiki/White-box_testing) and is the quickest way of
writing low level tests for specific classes or components. Implement unit tests

HumHub uses [Codeception](http://codeception.com/) as testing framework.

*ATTENTION: Some of the test libraries are developed for use with PHP 7 only*

Information about how to write tests with codeception are available here:

 - [Codeception Introduction](http://codeception.com/docs/01-Introduction)
 - [Yii Testing Guide](https://www.yiiframework.com/doc/guide/2.0/en/test-overview)

## Test Environment Setup

-  Install codeception ([http://codeception.com/install](http://codeception.com/install))
-  Composer codeception:

```
composer global require "codeception/codeception=2.0.*" "codeception/specify=*" "codeception/verify=*"
```

- Create a test database:

```
CREATE DATABASE `humhub_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

- Configure database access:

Configure the database connection for your test environment in `@humhub/tests/config/common.php`:


 ```
    'components' => [
	    'db' => [
	    'dsn' => 'mysql:host=localhost;dbname=humhub_test',
		    'username' => 'myUser',
		    'password' => 'myPassword',
		    'charset' => 'utf8',
    	], 
    ]
```

- Run Database Migrations:
   
```
cd protected/humhub/tests/codeception/bin
php yii migrate/up --includeModuleMigrations=1 --interactive=0
```

> Note: You'll have to run the migrations for your test environment manually in order to keep your test environment up to date.

- Install test environment:

`php yii installer/auto`

- Set `HUMHUB_PATH` system variable

The `HUMHUB_PATH` is used by your test environment in non core module tests to determine the HumHub root.
This is only required for non core module tests and can also be set in your modules test configuration `/tests/config/test.php`:

```
return [
    'humhub_root' => '/path/to/my/humhub/root',
];
```

## Test configuration

The settings of your default configuration files in the `$HUMHUB_PATH/protected/humhub/tests/config` directory can be overwritten for each module
within the corresponding `<module_root>/tests/config` files.

The following configuration files can be used to overwrite the test configuration:

 - The initial test configuration `test.php` is used to set general test settings as the path to your `humhub_root` or used fixtures.
 - The configuration of `common.php` is used for all suites and can be overwritten by settings in a suite configuration.
 - Suite specific test configurations (e.g. functional.php) are used to configure HumHub for a specific test suite.

The configurations for a suite will be merged in the following order:

 - `@humhub/protected/humhub/tests/config/functional.php`
 - `@myModule/tests/config/common.php`
 - `@myModule/tests/config/functional.php`
 - `@myModule/tests/config/env/myenv/common.php (if exists)`
 - `@myModule/tests/config/env/myenv/functional.php (if exists)`

### Environment Configuration

For running a test for a specific environment you'll have to set te `--env` argument for your test-run.

Example for running all functional tests of a tasks module in a master environment:

1. Create a file `@mymodule/tests/config/env/master/test.php` with the following content:

```
return [
    'humhub_root' => '/pathToMasterBranch'
];
```

2. If needed set further HumHub settings in `tasks/tests/config/env/master/funtional.php`
3. Run `codecept run functional --env master`

> Note: Your modules test.php configuration file should always set an 'modules' array with all non core modules needed for test execution.

> Note: You can specify multiple --env arguments in codeception, for module tests the first --env argument must contain the environment of your
`humhub_root` settings you want to use for this test run. 

## Run Tests:

### Run all core tests:

```
cd protected/humhub/tests/
codecept run
```

### Run core module test

```
cd protected/humhub/modules/user/tests
codecept run
```

### Run specific suite

```
cd protected/humhub/modules/user/tests
codecept run unit
```

### Run single test file

```
codecept run codeception/acceptance/TestCest
```

### Run single test function

```
codecept run codeception/acceptance/TestCest:testFunction
```

### Run non core module tests

To run non core module tests you have to edit at least the test config file under `<yourmodule>/tests/config/test.php`.
If your modules reside outside of the /protected/humhub/modules directory you'll have to set the following configurations:

In your `<yourmodule>/tests/config/test.php`:

```
return [
    // This will enable all provided modules for the testenvironment
    'modules' => ['myModuleId']
];
```

You may also want to extend the `moduleAutoloadPath` in your `<yourmodule>/tests/config/common.php`:

```
return [
    'params' => [
        'moduleAutoloadPaths' => ['/pathToMyModules']
    ]
];
```

### Run acceptance tests

`Phantom.js` or `Selenium` are required to run acceptance tests on your system.

> Note: If you are already running a webserver on port 8080, you do not need to start the test server described blow, because Humhub tests are run on port 8080. 
However if your `DocumentRoot` directory is not configured to directly open HumHub via `localhost` you have to do some adjustments 
in the `codeception.yml`  file of our `test` folder (`test-entry-url`) and the `acceptance.suite.yml` file. 
Alternatively start the test server as described below (in humhub root directory).

#### Phantom.js

- Start a Phantom.js server:

```
cd protected/vendor/bin
phantomjs --webdriver=44444
```

#### With chrome driver/selenium (recommended)

Download 

 - [chromedriver](http://chromedriver.chromium.org/downloads) 
 - [selenium standalone](https://www.seleniumhq.org/download/)
  
and copy them in the same directory.

Start selenium:

```
java -Dwebdriver.chrome.driver=chromedriver.exe -jar selenium-server-standalone-2.53.0.jar
```

Start test server:

```
cd /myhumHubInstallation
php -S localhost:8080
```

Run your acceptance test:

```
codecept run acceptance`
```
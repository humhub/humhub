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

4. Install test environment:

´´´
 cd protected/humhub/tests/codeception/bin
 php yii migrate/up --includeModuleMigrations=1 --interactive=0
 php yii installer/auto
´´´

5. Set HUMHUB_PATH system variable

You should set the HUMHUB_PATH environment which should point to your HumHub root directory you want to use for testing.
This is only required for non core module tests and can also be set in your modules test configuration ´/tests/config/test.php´:

´´´
return [
    'humhub_root' => '/path/to/my/humhub/root',
];
´´´

> Note: The test environment only works with HumHub v1.2 or later.

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

### Run single test

´´´
codecept run codeception/acceptance/TestCest:testFunction
´´´

### Run acceptance tests
#### with phantomjs

- Run phantomjs server (is installed with composer update)

cd protected/bin
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


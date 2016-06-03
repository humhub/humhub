Testing
====================

1. Install codeception
composer global require "codeception/codeception=2.0.*" "codeception/specify=*" "codeception/verify=*"

2. Create test Database:
CREATE DATABASE `humhub_test` CHARACTER SET utf8 COLLATE utf8_general_ci;

3. Migrate Up:
cd protected/humhub/tests/codeception/bin
php yii migrate/up --includeModuleMigrations=1 --interactive=0
php yii installer/auto

4. Build Tests:
cd protected/humhub/tests
codecept build

5. Run Tests:
cd protected/humhub/tests/
codecept run

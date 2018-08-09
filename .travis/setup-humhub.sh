#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

composer install --prefer-dist --no-interaction

npm install
grunt build-assets

cd ${TRAVIS_BUILD_DIR}/protected/humhub/tests

mysql -e 'CREATE DATABASE humhub_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
php codeception/bin/yii migrate/up --includeModuleMigrations=1 --interactive=0
php codeception/bin/yii installer/auto
php codeception/bin/yii search/rebuild
#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

# Install composer packages
composer install --prefer-dist --no-interaction

# Install node packages
npm install

# Build production assets
grunt build-assets

# Create mysql database
mysql -e 'CREATE DATABASE humhub_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'

# Run migrations, setup humhub and rebuild search index
cd ${TRAVIS_BUILD_DIR}/protected/humhub/tests
php codeception/bin/yii migrate/up --includeModuleMigrations=1 --interactive=0
php codeception/bin/yii installer/auto
php codeception/bin/yii search/rebuild
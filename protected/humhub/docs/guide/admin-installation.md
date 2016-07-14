Installation
============

## 1. Preparation

Create an MySQL Database, e.g.:

```sql
CREATE DATABASE `humhub` CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL ON `humhub`.* TO `humhub_dbuser`@localhost IDENTIFIED BY 'password_changeme';
FLUSH PRIVILEGES;
```

## 2. Get HumHub

### Via: Git/Composer

- Clone Git Repository

```
git clone https://github.com/humhub/humhub.git
```

- Install composer ([https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md))
- Navigate to your HumHub webroot and fetch dependencies

```
php composer.phar global require "fxp/composer-asset-plugin:~1.1.1"
composer update
```

### Via: Download Package

Download package at [http://www.humhub.org/downloads](http://www.humhub.org/downloads)  and extract it somewhere into your htdocs folder.

## 3. Setting up

### File Modes / Permissions

Make the following directories/files writable by the webserver
- /assets
- /protected/config/
- /protected/modules
- /protected/runtime
- /uploads/*

Make the following files executable:
 - /protected/yii
 - /protected/yii.bat

### Start Installer

Open installation in your browser (e.g. [http://localhost/humhub](http://localhost/humhub))


## 4. Fine Tuning

### E-Mail Configuration

Depending on your environment which you are using you may want specify a local or remote SMTP Server.
You can change this settings at `Administration -> Mailing -> Server Settings`.

By default PHP Mail Transport is used. <http://php.net/manual/en/mail.setup.php>


### Enable Url Rewriting (Optional)

1. Rename **.htaccess.dist ** to **.htaccess**
2. Modify local configuration (protected/config/common.php)

```php
<?php

return [
    'components' => [
        'urlManager' => [
            'showScriptName' => false,
            'enablePrettyUrl' => true,
        ],
    ]
];

```  

### Enable Cron Jobs

Daily cron command: 
> yii cron/daily

Hourly cron command:
> yii cron/hourly

Example Tab:

```
30 * * * * /path/to/humhub/protected/yii cron/hourly >/dev/null 2>&1
00 18 * * * /path/to/humhub/protected/yii cron/daily >/dev/null 2>&1
```

### Check Directory Protection

**Make sure following directories are not accessible throu webserver!**

(These folders are protected by default with ".htaccess")

- protected
- uploads/file

### Disable Errors / Debugging

- Modify *index.php* in humhub root directory
     
```php
// comment out the following two lines when deployed to production
// defined('YII_DEBUG') or define('YII_DEBUG', true);
// defined('YII_ENV') or define('YII_ENV', 'dev');
```

- Delete *index-test.php* in humhub root directory if exists
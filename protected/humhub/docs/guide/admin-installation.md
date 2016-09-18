Installation
============

## Preparation

Create a MySQL Database, e.g.:

```sql
CREATE DATABASE `humhub` CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL ON `humhub`.* TO `humhub_dbuser`@localhost IDENTIFIED BY 'password_changeme';
FLUSH PRIVILEGES;
```

> Note: Do not forget to change the `humhub_dbuser` and `password_changeme` placeholders!

## Get HumHub

### Via: Download Package

The easiest way to get HumHub, is the direct download of the complete package under [http://www.humhub.org/downloads](http://www.humhub.org/downloads).
This package already includes all external dependencies and doesn't require a composer update.
After the download completed, just extract the package into the htdocs folder of your webserver.

### Via: Git/Composer

In order to be able to install a branch fetched by git, you'll have to run a composer update to download external dependencies.

 - Clone Git Repository:

```
git clone https://github.com/humhub/humhub.git
```

 - Switch to stable branch (recommended):

```
git checkout stable
```

 - Install composer ([https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md))
 - Navigate to your HumHub webroot and fetch dependencies:

```
php composer.phar global require "fxp/composer-asset-plugin:~1.1.1"
php composer.phar update
```

> Note: The composer update may have to be executed again after an update of your local repository by a git pull. Read more about updating ([Update Guide](admin-updating.html#gitcomposer-based-installations))

## Setting up

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

**Make sure the following directories are not accessible through the webserver!**

(These folders are protected by default with ".htaccess")

- protected
- uploads/file

### Start Installer

Open the installation guide in your browser (e.g. [http://localhost/humhub](http://localhost/humhub))


## Fine Tuning

### E-Mail Configuration

Depending on your environment you are using, you may want to specify a local or remote SMTP Server.
You can change the mail-server settings under `Administration -> Mailing -> Server Settings`.

By default the PHP Mail Transport is used. <http://php.net/manual/en/mail.setup.php>


### Enable Url Rewriting (Optional)

Rename **.htaccess.dist ** to **.htaccess**
Modify the local configuration (protected/config/common.php):

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

 - Daily cron command: `> yii cron/daily`
 - Hourly cron command: `> yii cron/hourly`

Example Tab:

```
30 * * * * /path/to/humhub/protected/yii cron/hourly >/dev/null 2>&1
00 18 * * * /path/to/humhub/protected/yii cron/daily >/dev/null 2>&1
```

### Disable Errors / Debugging

 - Modify *index.php* in your humhub root directory
     
```php
// comment out the following two lines when deployed to production
// defined('YII_DEBUG') or define('YII_DEBUG', true);
// defined('YII_ENV') or define('YII_ENV', 'dev');
```

 - Delete *index-test.php* in your humhub root directory if exists
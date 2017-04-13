Installation (Developers)
=========================

> Warning: This installation method allows you to easily fetch the latest version from our 

Preparation
-----------

Create a MySQL Database, e.g.:

```sql
CREATE DATABASE `humhub` CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL ON `humhub`.* TO `humhub_dbuser`@localhost IDENTIFIED BY 'password_changeme';
FLUSH PRIVILEGES;
```

> Note: Do not forget to change the `humhub_dbuser` and `password_changeme` placeholders!


Get HumHub
----------

In order to be able to install a branch fetched by git, you'll have to run a composer update to download external dependencies.

 - Clone Git Repository:

```
git clone https://github.com/humhub/humhub.git
```

 - Install composer ([https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md))
 - Navigate to your HumHub webroot and fetch dependencies:

```
php composer.phar global require "fxp/composer-asset-plugin:~1.3"
php composer.phar update
```

> Note: The composer update may have to be executed again after an update of your local repository by a git pull. Read more about updating ([Update Guide](admin-updating.html#gitcomposer-based-installations))



File Settings & Permissions
---------------------------


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


Start Web Installer
---------------

Open the installation guide in your browser (e.g. [http://localhost/humhub](http://localhost/humhub))





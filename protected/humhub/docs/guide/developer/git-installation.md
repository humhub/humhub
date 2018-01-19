Installation (Developers)
=========================
> Warning: This installation method allows you to fetch the latest branch from the repository which may not stable enough for production use.  

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
 - Install [git](https://git-scm.com/)
 - Clone git Repository:

```
git clone https://github.com/humhub/humhub.git
```
 - Install composer ([https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md))
 - Navigate to your HumHub webroot and fetch dependencies:
```
php composer.phar global require "fxp/composer-asset-plugin:~1.3"
php composer.phar update
```

> Note: The composer update may have to be executed again after an update of your local repository by a git pull. Read more about updating ([Update Guide](../admin/updating.md))

 - Follow further instructions of the [Installation Guide](../admin/installation.md)



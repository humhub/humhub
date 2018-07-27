Git/Composer Installation
=========================

The following guide describes a git based installation of the HumHub platform. Please note that this is only recommended for
developers and testers and should not be used as production installation. For production systems, please follow the [Installation Guide for Administrators](../admin/installation.md).

Database Setup
-----------
Please follow the [Database Setup Section](../admin/installation.md#database-setup) of the administration installation guide.

Get HumHub
----------
 - Install [git](https://git-scm.com/)
 - Clone the git Repository:

```
git clone https://github.com/humhub/humhub.git
```

 - Install composer ([https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md))
 - Navigate to your HumHub webroot and fetch dependencies:
 
```
composer install
```

> Note: The composer update may have to be executed again after an update of your local repository by a git pull. Read more about updating ([Update Guide](../admin/updating.md))
> Note: Since HumHub 1.3 you have to build the production assets manually, please see the [Build Assets Section](build.md#build-assets) for more information.

 - Follow further instructions of the [Installation Guide](../admin/installation.md)



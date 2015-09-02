Updating
=========

If you are using custom themes or modules, please check these guides before updating!
* [Theme Migration](https://github.com/humhub/humhub/blob/master/protected/docs/guide/theming/migrate.md)
* [Module Migration](https://github.com/humhub/humhub/blob/master/protected/docs/guide/developer/migrate.md)

## The Git Way

* First, backup ALL your files & database
* Pull latest Git files `git pull`
* Run `php /path/to/humhub/protected/yiic update`

## The Source/Download Way

* First, backup ALL your files & database
* Download <https://github.com/humhub/humhub/archive/master.zip>
* Delete current installation files (Backup? :-))
* Extract download package
* Restore from backup:
    - /path/to/humhub/uploads/file
    - /path/to/humhub/uploads/profile_image
    - /path/to/humhub/protected/runtime
    - /path/to/humhub/protected/config/local/_settings.php
    - /path/to/humhub/protected/modules
    - /path/to/humhub/themes (if any)
 Check file permissions (see [Installation](installation.md))
* Run `php /path/to/humhub/protected/yiic update`

## From 0.10.1 to 0.11.0
You may also need to run these commands to rebuild space, and user lists for the Directory
* Run `php /path/to/humhub/protected/yiic search_rebuild`
* Run `php /path/to/humhub/protected/yiic search_optimize`

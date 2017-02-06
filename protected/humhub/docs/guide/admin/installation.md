Installation 
============

> Note: It's also possible to install and build HumHub directly from our **Git Repository**.
Please see [Developer Installation](../developer/git-installation.md) for more details.



Database
--------

Create a MySQL Database, e.g.:

```sql
CREATE DATABASE `humhub` CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL ON `humhub`.* TO `humhub_dbuser`@localhost IDENTIFIED BY 'password_changeme';
FLUSH PRIVILEGES;
```

> Note: Do not forget to change the `humhub_dbuser` and `password_changeme` placeholders!

> Warning: Make sure to use the **utf8_general_ci** database collation!


Download HumHub Core Files
---------------------------

The easiest way to get HumHub, is the direct download of the complete package under [http://www.humhub.org/downloads](http://www.humhub.org/downloads).

After the download completed, just extract the package into the htdocs folder of your webserver.


File Permissions
----------------------------

Make the following directories/files writable by the webserver
- /assets
- /protected/config/
- /protected/modules
- /protected/runtime
- /uploads/*

Make the following files executable:
 - /protected/yii
 - /protected/yii.bat


Web Installer
-------------------

Open the installation guide in your browser (e.g. [http://localhost/humhub](http://localhost/humhub))

> Warning: Don't forget to proceed with the [Configuration](installation-configuration.md) chapter after the installation.



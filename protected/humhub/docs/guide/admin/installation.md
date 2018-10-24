Installation 
============

This guide describes the installation of the HumHub package, which can be downloaded from the 
[HumHub homepage](https://www.humhub.org/download). The packaged version of HumHub contains all required 
dependencies and external libraries and can directly be installed.
 
It's also possible to install and build HumHub manually by cloning our [Git Repository](https://github.com/humhub/humhub).
Please see the [Developer Installation](../developer/environment.md) for more details.

Database Setup
--------
Create a MySQL Database, e.g.:

```sql
CREATE DATABASE `humhub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL ON `humhub`.* TO `humhub_dbuser`@localhost IDENTIFIED BY 'password_changeme';
FLUSH PRIVILEGES;
```

> Note: Do not forget to change the `humhub_dbuser` and `password_changeme` placeholders!

> Note: `utf8mb4` is supported since HumHub v1.3 and is preferred over `utf8` since MySQL 5.5.3 
please refer to the [mysql documentation](https://dev.mysql.com/doc/refman/5.5/en/charset-unicode-utf8mb4.html) for more information.


Download HumHub Core Files
---------------------------

The easiest way to get HumHub, is by directly downloading the complete package under [https://www.humhub.org/download](https://www.humhub.org/download).

After the download, extract the package into the `htdocs` folder of your webserver.


File Permissions
----------------------------

Make sure the following directories and files are **writable** by the webserver:

- /assets
- /protected/config/
- /protected/modules
- /protected/runtime
- /uploads/*

The following files need to be **executable**:

 - /protected/yii
 - /protected/yii.bat
 
### !Important Protected Directories
 
 Make sure the following directories are **not accessible by web**:
 
 > Info: By default the following two folders are protected with a ".htaccess" file.
 
 - /protected
 - /uploads/file
 
 Make sure files in the following directory are **not executable**:
 
 - /uploads
 - /assets
 - /static


Web Installer
-------------------

Open the installation guide in your browser (e.g. [http://localhost/humhub](http://localhost/humhub))

> Note: Don't forget to proceed with the [Configuration](installation-configuration.md) chapter after the installation.

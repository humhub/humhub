Requirements
============
> Note: All vital requirements will also be also checked during the web installation.

Server Requirements
-------------------
* Shell access (e.g. ssh) to server  
* PHP 5.6 - 7.2.x  (PHP 7.2+ is supported since HumHub 1.3)
* MySQL (5.5 or later) or MariaDB with utf8mb4 character set support and InnoDB storage engine installed
* A minimum 500 MB of free disk space
* A minimum 64 MB of memory allocated to PHP
* A minimum of 50 MB of database space

Required PHP Extensions
-----------------------
* PHP GD Extension (With JPEG and PNG support)
* PHP CUrl  Extension (w/ SSL Support) <https://secure.php.net/manual/en/curl.setup.php>
* PHP Multibyte String Support <https://secure.php.net/manual/en/mbstring.setup.php> 
* PHP PDO MySQL Extension (https://secure.php.net/manual/en/ref.pdo-mysql.php)
* PHP Zip Extension (https://secure.php.net/manual/en/book.zip.php)
* PHP EXIF Extension (https://secure.php.net/manual/en/book.exif.php)
* PHP INTL Extension (https://secure.php.net/manual/en/intro.intl.php) (min ICU v49 see [Yii2 Internationalization](https://github.com/yiisoft/yii2/blob/master/docs/guide/tutorial-i18n.md#setting-up-your-php-environment-))
* PHP FileInfo Extension (https://secure.php.net/manual/en/fileinfo.installation.php)

Optional PHP Extensions
-----------------------
* ImageMagick
* PHP LDAP Support
* PHP APC
* PHP Memcached

Database
--------
The following privilege are required for the HumHub database user:

- SELECT
- INSERT
- DELETE
- UPDATE
- CREATE
- ALTER
- INDEX
- DROP
- REFERENCES

Further Requirements
--------
Some hosts block certain PHP function, please make sure your environment allows the execution of:

 - `set_time_limit()`
 - `escapeshellcmd()`
 

Requirements
============

> Note: All vital requirements will be also checked during the web installer.

Server Requirements
-------------------

* Shell access (e.g. ssh) to server  
* PHP 5.6 or later
* MySQL (5.1 or later) or MariaDB with InnoDB storage engine installed
* A minimum 500 MB of free disk space
* A minimum 64 MB of memory allocated to PHP
* A minimum of 50 MB of database space


Required PHP Extensions
-----------------------
* PHP CUrl  Extension (w/ SSL Support) <http://de1.php.net/manual/en/curl.setup.php>
* PHP Multibyte String Support <http://php.net/manual/en/mbstring.setup.php> 
* PHP PDO MySQL Extension (http://www.php.net/manual/en/ref.pdo-mysql.php)
* PHP Zip Extension (http://php.net/manual/en/book.zip.php)
* PHP EXIF Extension (http://php.net/manual/en/book.exif.php)
* PHP INTL Extension (http://php.net/manual/en/intro.intl.php)
* PHP FileInfo Extension (http://php.net/manual/en/fileinfo.installation.php)


Optional PHP Extensions
-----------------------

* ImageMagick
* PHP LDAP Support
* PHP APC
* PHP Memcached


Database
--------
The database user you tell HumHub to connect with must have the following privileges:

- SELECT
- INSERT
- DELETE
- UPDATE
- CREATE
- ALTER
- INDEX
- DROP
- REFERENCES

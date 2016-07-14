Yii Framework 2 composer extension Change Log
=============================================

2.0.4 February 06, 2016
-----------------------

- Bug #7735: Composer failed to install extensions with multiple base paths in "psr-4" autoload section (cebe)
- Enh #2: Better error handling for the case when installer is unable to change permissions (dbavscc)
- Enh #3: `loadExtensions()` and `saveExtensions()` now access `EXTENSION_FILE` constant with late static binding (karneds)


2.0.3 March 01, 2015
--------------------

- no changes in this release.


2.0.2 January 11, 2015
----------------------

- no changes in this release.


2.0.1 December 07, 2014
-----------------------

- no changes in this release.


2.0.0 October 12, 2014
----------------------

- no changes in this release.


2.0.0-rc September 27, 2014
---------------------------

- Bug #3438: Fixed support for non-lowercase package names (cebe)
- Chg: Added `yii\composer\Installer::postCreateProject()` and modified the syntax of calling installer methods in composer.json (qiangxue)

2.0.0-beta April 13, 2014
-------------------------

- Bug #1480: Fixed issue with creating extensions.php when php opcache is enabled (cebe)
- Enh: Added support for installing packages conforming to PSR-4 standard (qiangxue)

2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.

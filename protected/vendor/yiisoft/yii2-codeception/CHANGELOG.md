Yii Framework 2 Codeception extension Change Log
================================================

2.0.5 March 17, 2016
--------------------

- Bug #7: Extension won't create new app instance if it already exists, for example was created in module (kaiserfedor)
- Bug #13: Close database connection and session on destroying application (kaiserfedor)


2.0.4 May 10, 2015
------------------

- Enh #1: Allow to configure DI Container in configuration files (leandrogehlen)


2.0.3 March 01, 2015
--------------------

- Bug #6978: DI Container is not reset when destroying application in functional tests (ivokund)


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

- no changes in this release.


2.0.0-beta April 13, 2014
-------------------------

- Initial release.
- Enh: yii\codeception\TestCase now supports loading and using fixtures via Yii fixture framework (qiangxue)
- New #1956: Implemented test fixture framework (qiangxue)
- New: Added yii\codeception\DbTestCase (qiangxue)

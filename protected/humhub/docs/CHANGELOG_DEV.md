HumHub Change Log - v1.3-dev Branch
===================================

1.3.0-beta.1  (Not released yet)
--------------------------------

- Enh: Added file search indexing
- Enh: Updated composer.json (acs-ferreira)
- Chg: Switched from Composer FXP plugin to Asset Packagist repository
- Enh: Committed composer.lock
- Enh: Refactored ContentContainer Controller
- Chg: Added ContentContainer ModuleManager, instead of individual handling (Space/User)
- Fix: Rebind LDAP connection after successful login with administrative user
- Enh: Make utf8_mb4 as default database charset
- Enh: Moved queueing into own submodule and updated to yii2/queue extension
- Enh: Added user soft deletion without contributions
- Enh: Moved user deletion into asynchronous tasks
- Enh: Improved user grid view design (Administration, User Approval, Space Members)
- Enh: Moved SyncUsers (LDAP) and session table cleanup handling into ActiveJob
- Enh: Added Push live module driver using Redis and Node.JS
- Enh: Added tooltip option to space Image widget.
- Enh: Added option ContentContainerController to restrict container type



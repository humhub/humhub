HumHub Changelog
================

1.15.0 (Unreleased)
-------------------
- Fix #6259: Add json & pdo extensions as requirement; updating composer dependencies and node modules
- Fix #6192: Where Group::getAdminGroupId() would sometimes return int, sometimes string
- Enh #6260: Improve migration class
- Fix #6199: Module manager Add types to properties
- Fix #6189: Module settings survive deactivation in cache
- Enh #6236: Logging: Show log entries from migrations with category migration
- Fix #6216: Spaces icon in admin menu
- Fix #6229: Bug on saving forms: Zend OPcache API is restricted by "restrict_api"
- Enh #6240: Add ability to set showAtDashboard in SpaceMembership::addMember method 
- Fix #6243: Do not send notification when ApprovalRequest is not valid
- Enh #6215: Added `LongRunningActiveJob` to avoid timeout for long running queue jobs
- Enh #6253: Remove `DefaultSwipeOnMobile` checkbox
- Enh #10: Added `collapsedPostHeight` to the Post module for set collapsed post default height

HumHub Changelog
================

1.15.0 (Unreleased)
-------------------
- Enh #6270: Add tests for SettingsManager
- Enh #6272: Always return integer from settings, if value can be converted
- Fix #6267: SettingsManager::flushContentContainer() only clears the collection in the current instance, not the underlying cache
- Enh #6271: Add input and type checks, as well as strict types to SettingsManager
- Fix #6266: BaseSettingsManager::deleteAll() does use prefix as wildcard
- Fix #6259: Add json & pdo extensions as requirement; updating composer dependencies and node modules
- Fix #6192: Where Group::getAdminGroupId() would sometimes return int, sometimes string
- Enh #6260: Improve migration class
- Fix #6199: Module manager Add types to properties
- Fix #6189: Module settings survive deactivation in cache
- Enh #6236: Logging: Show log entries from migrations with category migration
- Fix #6216: Spaces icon in admin menu
- Fix #6229: Bug on saving forms: Zend OPcache API is restricted by "restrict_api"
- Enh #6240: Add ability to set showAtDashboard in SpaceMembership::addMember method
- Enh #5668: Allow Admin to sort the Spaces in a custom order
- Enh #29: AutoStart Tour for new Users
- Fix #6243: Do not send notification when ApprovalRequest is not valid
- Enh #6215: Added `LongRunningActiveJob` to avoid timeout for long running queue jobs
- Enh #6253: Remove `DefaultSwipeOnMobile` checkbox
- Enh #10: Added `collapsedPostHeight` to the Post module for set collapsed post default height
- Enh #6285: Change background color for confirmation of oembed content
- Enh #6289: Refactored UserWall and Wall widgets
- Fix #44: Mail Module Indicator Problem
- Fix #6299: Fix ambiguous space sort order column
- Enh #6298: Move the "Write a new comment" field style to a generic field that can be used by other modules

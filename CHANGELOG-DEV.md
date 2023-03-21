HumHub Changelog (DEVELOP)
==========================

1.14.0-beta.2 (Unreleased)
------------------------------
- Enh #6173: Theme variables `background-color-highlight` and `background-color-highlight-soft`
- Fix #6099: Empty buttons in GlobalConfirmModal footer
- Fix #6100: Broken area reference in some modal boxes
- Enh #6171: Make email in user administration and user approval clickable
- Enh #6169: Replace deprecated `yii\base\BaseObject::className()`

1.14.0-beta.1 (March 10, 2023)
------------------------------

- Enh #4803: Added more panel styles (`panel-info` and `panel-primary`)
- Enh #5972: Removed old vendor CSS prefixes (e.g. `-moz` or `-webkit`)
- Enh #6006: Fix activity settings test
- Fix #6018: Disable profile fields textarea and checkbox list when they are not editable
- Ehn #6017: Hide Password Tab in administration for LDAP users
- Enh #6031: On users list grid, show Auth mode badge only for sys admins
- Enh #6033: Moved more logic into `AbstractQueryContentContainer`
- Enh #6035: Added Estonian language
- Fix #5956: Display all newer comments after current comment
- Enh #6061: Administration: Add a confirmation on profile field delete button
- Enh #5699: Allow users to invite by link
- Enh #6081: Added corresponding CSS variables for LESS color variables 
- Fix #6022: Fix Changelog Link with new Marketplace URL
- Enh #5973: Stylesheet Prefix Cleanup and removed temporary style
- Enh #6077: Always display content tabs
- Enh #5263: Allow members of groups other than system admin to view all content (groups that can manage users for profile content and groups that can manage spaces for space content)
- Enh #6102: Also allow Messages module to inject new message count into page title
- Enh #6109: Added enabled Pretty URL as self test
- Fix #6113: Ensure `displayNameSub` doesn't return NULL values 
- Enh #5904: Make Dynamic Post Font Size Optional
- Enh #6109: Added enabled Pretty URL as self test
- Enh #6119: Added UserInfoWidget for User Notification Settings 
- Enh #6116: Scheduled content publishing
- Enh #6135: Added new ContentState and Content events (e.g. SoftDelete) 
- Enh #5625: Update jQuery UI to version 1.13
- Enh #6144: Added ability to change/disable `Forgot your password?` link
- Fix #4988: Unable to archive space on Form Validation errors
- Enh #6123: Added check for `proc_open` function in Requirement Checker 
- Enh #6149: Added `AuthClientUserService` and `AuthClientService` instead of `AuthClientHelpers`

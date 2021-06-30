1.9.0-beta.2 (Unreleased)
----------------------------

- Fix #5128: Prerequisites: Increase PHP Min version to 7.3
- Fix #5136: Fix get value of user profile fields with types "Checkbox List" and "Checkbox"
- Fix #5137: Fix convert to short integer on PHP 8
- Fix #5149: Use a link mode for space button "Join" from space header


1.9.0-beta.1 (June 15, 2021)
----------------------------

Note: HumHub version 1.9+ requires PHP 7.3 or higher!

- Fix #5071: Add CLI hint to PCTL Requirements Warning


1.9.0-beta.1 (June 15, 2021)
----------------------------

Note: HumHub version 1.9+ requires PHP 7.3 or higher!

- Enh #3733: Forbid to open AJAX actions as separate page
- Enh #677: Allow to create new database and set database port on installation wizard
- Fix #4877: Check for writable uploads/profile_image directory
- Enh #4868: Reset email summaries / notifications settings for all users
- Enh #4884: New Space module setting to allow all users to add users without invite
- Enh #4902: Added CodeMirror and created form field widget 
- Enh #4964: New CLI command to delete users 
- Enh #4871: Configurable default timezone for guests
- Enh #5019: Alternative `DashboardMemberStreamFilter` based on Live module `LegitmationIDs`
- Fix #4626: Visibility of content in profile stream of archived spaces
- Chg #5016: Allow impersonate for `ManageUsers` permission
- Enh #5043: Improved Space membership lookup caching
- Enh #4935: Render images in email messages
- Enh #5037: `RichTextToShortTextConverter` renders images as `[Image]`
- Enh #5042: Improved Space/User PrettyURL performance
- Enh #4958: Add possibility to register purchased modules via CLI
- Enh #4894: Implemented `.label-light` label variant
- Enh #5012: Space: Allow change space owner also for users `ManageSpaces` with permission
- Enh #5045: Removed deprecated `Yii::$app->formatterApp` component
- Enh #5026: Tests for tokenized image urls in email message
- Enh #5049: Required profile field should not be required in administration
- Enh #5065: Add checking for php `PCNTL` extension
- Enh #5073: Enhance Rest API tests
- Fix #5078: Fix enabling of REST module on run API tests
- Enh #4776: Added `acknowledge` option to all relevant forms
- Enh #4757: Updated to `PHPUnit 8.5`
- Enh #4790: Added more logging on authentication errors
- Fix #5054: Force `InnoDB` Table Engine on migration and added engine configuration option
- Enh #4862: Tag picker for Space and User containers
- Enh #4927: Hide post input field on single entry stream
- Enh #5062: Show user ID on admin user edit page
- Enh #4848: Include space tags in the space’s “About” page 
- Enh #5061: Preserve linebreaks and added "ReadMore" option in Logging
- Enh #4982: Skip DN List to avoid duplicated ldap error logs 
- Fix #5088: Set max length option to space description input
- Enh #5089: Allow to follow users independently of friendship
- Enh #34: Removed membership icon from my Spaces chooser
- Enh #117: Show/Hide the “Following” buttons depending on updated friendship/membership state after AJAX action
- Enh #5102: Use short number format for Space members count on Space Directory
- Fix #5101: Fix to allow admin editing of not required profile fields
- Enh #123: New sorting option "Default" for people directory page
- Fix #122: Fix button "Load more" initialization on Pjax loading
- Enh #5114: Added `EVENT_BEFORE_CHECKING_USER_STATUS` in `AuthController.php` to give the possibility to add an event before checking the user status
- Fix #5122: `RichText::output` produces `p` HTML element instead of `div`

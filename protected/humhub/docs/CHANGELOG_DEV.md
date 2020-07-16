HumHub Change Log
=================


1.6.0-beta.1 (July 16, 2020)
----------------------------

- Enh: Improved performance of cli marketplace module updater
- Fix #4054: Duplicate "font-weight" value
- Enh: Prevent 100% image height in blueimp gallery
- Chg #4170: Updated codeception to v4.1.6
- Chg #4138: Updated jQuery to v3.5.1
- Chg #4158: Cleanup post table removed unused column  
- Fix #4182: Native edge password reveal icons interferes with custom one
- Fix #4173: Notification overview HTML compliant issue
- Enh #4191: Added SortOrder Form Input Field
- Enh: Added `ContentVisibilitySelect` ActiveField widget for content forms
- Enh #4213: Only render topic chooser if there are topics available or user can create topics
- Enh: Added `humhub\modules\ui\form\widgets\ActiveField:preventRendering` to manage render state within field classes
- Enh: Added `humhub\modules\ui\form\widgets\JsInputWidget:emptyResult()` helper to manage render state of JsInputWidget
- Enh: Added `humhub\modules\ui\form\widgets\JsInputWidget:field` in order to access ActiveField instances within JsInputWidget
- Enh #4216: Added `humhub\modules\ui\filter\widgets\DropdownFilterInput` in order to support dropdown stream filters
- Enh: Added support for non-free marketplace modules without a fixed price 
- Enh: Show more information about installed module in marketplace when possible. Instead of limited README.md 
- Enh #3923: Add ability to disable profile stream
- Enh #4222: Added virtual profile fields to display users e-mail address and username
- Enh #4194: Increased max pinnable space content
- Enh #4194: Make max pinnable content configurable on space/profile level
- Chg #4228: Removed unnecessary `ContentActiveRecord:initContent`
- Fix #4229: `Space::canAccessPrivateContent()` throws error for guest user if `globalAdminCanAccessPrivateContent` setting is true
- Fix #4227: Removed redundant code from `humhub.ui.widget.js`
- Fix #4232: Metadata request creates guest session if CSP nonce header is enabled
- Enh #4234: Enhanced custom test environment configuration in `@protected/humhub/tests/config/env/env.php` file
- Fix #4233: `humhub\modules\web\security\helpers\Security:setNonce()` does not remove nonce session value if nonce is null
- Fix #4235: Misleading error message 'Unable to determine dataType from response' logged on ajax error
- Enh #4238: Added module marketplace option for Community Modules. Updated third-party Module disclaimer
- Enh #4238: Added module marketplace option to enable module beta version updates
- Fix #4199: Pinned posts of other spaces are excluded from profile stream
- Enh #3995: Added additional user profile stream filter to include or exclude non profile stream content
- Enh: Added `humhub\modules\stream\actions\Stream:initQuery` to manage query filter in subclasses
- Enh: Make profile content archivable
- Enh: Archived stream filter now only included archived content

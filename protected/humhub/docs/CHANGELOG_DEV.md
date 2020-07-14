HumHub Change Log
=================


1.6.0-beta.1 (July 1, 2020)
---------------------------

- Enh: Improved performance of cli marketplace module updater
- Fix #4054: Duplicate "font-weight" value
- Enh: Prevent 100% image height in blueimp gallery
- Chg #4170: Updated codeception to v4.1.6
- Chg #4138: Updated jQuery to v3.5.1
- Chg #4158: Cleanup post table removed unused column  
- Fix #4182: Native edge password reveal icons interferes with custom one
- Fix #4173: Notification overview HTML compliant issue
- Enh #4213: Only render topic chooser if there are topics available or user can create topics
- Enh: Added `humhub\modules\ui\form\widgets\ActiveField:preventRendering` to manage render state within field classes
- Enh: Added `humhub\modules\ui\form\widgets\JsInputWidget:emptyResult()` helper to manage render state of JsInputWidget
- Enh: Added `humhub\modules\ui\form\widgets\JsInputWidget:field` in order to access ActiveField instances within JsInputWidget
- Enh #4216: Added `humhub\modules\ui\filter\widgets\DropdownFilterInput` in order to support dropdown stream filters
- Enh #4222: Added virtual profile fields to display users e-mail address and username
- Enh #4194: Increased max pinnable space content
- Enh #4194: Make max pinnable content configurable on space/profile level
- Chg #4228: Removed unnecessary `ContentActiveRecord:initContent`
- Fix #4229: `Space::canAccessPrivateContent()` throws error for guest user if `globalAdminCanAccessPrivateContent` setting is true
- Fix #4227: Removed redundant code from `humhub.ui.widget.js`

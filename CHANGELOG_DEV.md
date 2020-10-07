HumHub Changelog
================

1.7.0 (Unreleased)
-----------------------
- Fix #4327: Internationalization of user approval/decline message text
- Fix #4139: Dashboard post input on mobile cuts editor menu
- Fix #4328: Top navigation overlaps post input richtext menu on dashboard page
- Fix #4257: Notification dropdown text breaks after notification image on mobile
- Enh #4341: Simplified console controller map 
- Fix #4272: Guess timezone for new accounts
- Fix #4230: Auto enable "Hide file info (name, size) for images on wall" option
- Chg: Move CHANGELOG to humhub root
- Fix #4330: Allow users with permission ManageUsers to modify profile images
- Enh #4331: Added 'break-all' stylesheet option to stream entry layouts
- Enh #4179: Removed deprecated mysql character set in log table migration
- Enh #4324: Improved line break for menu entries with many characters
- Fix #4186: Add Timezone validation rules to admin prerequisite view
- Enh #4378: Implemented generic ContentContainerActiveRecord::is() function
- Enh #4310: Add "Can Like" Permission 
- Fix #4111: Issues Cropping Images
- Enh #4283: Implemented UserMemberSince and UserLastLogin user's profile virtual fields 
- Fix #4385: Tour broken when profile start page is changed
- Enh #3882: Rework of wall stream entry widget design and API
- Enh #3882: Introduction of alternative `WallStreamModuleEntry` widget for collaborative content types
- Chg #4397: Deprecated old wall entry widget and related stream logic (see `humhub\modules\stream\actions\LegacyStreamTrait.php`)
- Fix #4391: ActiveRecord `created_at` and `updated_at` contains invalid value after save
- Chg #4397: Default theme color alignment and new `@link` color variable
- Enh #4419: Implementation of view context http header `HUMHUB-VIEW-CONTEXT`
- Fix #4420: Uncaught Throwable destroys search layout
- Enh #4421: Added `Html::addTooltip()` to add tooltips to an option array
- Fix #4422: Invalid Html semantic in activity stream (`li` wrapped by `a`)
- Enh #4423: Implemented icon alias configuration in ui module class
- Enh #4424: Posts content with short text is emphasized
- Enh #4425: Use of accessibility compatible icon tooltips
- Fix #4408: JPlayer on mobile overflows stream content
- Fix #4382: Use of proper word break style
- Fix #3566: Bug in models/filetype/CheckboxList.php
- Enh #4401: Allow to use less variable name in value of another less variable
- Fix #4434: Fix title quoting for space icons in widget “Member in these spaces”
- Fix #4428: Replace db Expression time now with func date('Y-m-d G:i:s')
- Enh #4370: Add "summary" to `Space` model
- Enh #4370: Add `humhub\modules\space\widgets\MyMembership` to manage render user state in a space
- Enh #4370: Add `humhub\modules\space\widgets\AboutPageSidebar` to manage about page sidebar
- Enh #4370: Add `About Space` page 


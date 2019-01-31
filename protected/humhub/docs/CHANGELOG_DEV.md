HumHub Change Log (DEVELOP)
=================


1.4
---

- Enh: GroupPermissionManager - allow to query users by given permission
- Enh: Automatic migrate DB collations from utf8 to utf8mb4
- Enh: Added Icon abstraction layer
- Enh: Moved from bower to npm assets
- Chng: Removed `jquery-placeholder` asset and dependency
- Chng: Removed `atwho` asset and dependency
- Cnng: Removed old IE support
- Fix #2946: Use Yii2 default timezone handling
- Chng #2164: Removed MSN & Google+ social bookmarks during setup
- Enh: Added a user module configuration for setting password strength rules (Baleks)
- Fix #3103 Password recovery links pjax layout issue
- Chng: New Menu and MenuEntry rendering
- Enh: Added Icon abstraction `humhub\modules\ui\icon\widgets\Icon`
- Enh: Added `humhub\libs\Html::addPjaxPrevention()` for link options
- Enh: Added obj support for `humhub\libs\Sort`

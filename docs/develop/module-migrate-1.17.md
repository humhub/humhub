# Module Migration — Version 1.17

## Version 1.17.3

### Deprecated
- `\humhub\modules\user\Module::$invitesTimeToLiveInDays` use `\humhub\modules\admin\Module::$cleanupPendingRegistrationInterval` instead

## Version 1.17.2

### Behaviour change

- Method signature changed - `humhub\modules\user\models\fieldtype\BaseType::getUserValue(User $user, bool $raw = true, bool $encode = true): ?string`  

- Constructor changed - `humhub\modules\user\models\forms\Registration` and properties (`$enablePasswordForm`, `$enableMustChangePassword`, `$enableEmailField`) are now private

## Version 1.17 (January 2024)

### Behaviour change

- Forms in modal box no longer have focus automatically on the first field. [The `autofocus` attribute](https://developer.mozilla.org/docs/Web/HTML/Global_attributes/autofocus) is now required on the field. More info: [#7136](https://github.com/humhub/humhub/issues/7136)
- The new "Manage All Content" Group Permission allows managing all content (view, edit, move, archive, pin, etc.) even if the user is not a super administrator. It is disabled by default. It can be enabled via the configuration file, using the `\humhub\modules\admin\Module::$enableManageAllContentPermission` option.
- System admins are allowed, in all cases (even when `enableManageAllContentPermission` is disabled), to edit and delete content in other Profile streams.
- Users allowed to "Manage Users" can no longer move all content: they need to be allowed to "Manage All Content".

### New
- CSS variables: `--hh-fixed-header-height` and `--hh-fixed-footer-height` (see [#7131](https://github.com/humhub/humhub/issues/7131)): these variables should be added to custom themes in the `variables.less` file to overwrite the fixed header (e.g. the top menu + margins) and footer heights with the ones of the custom theme.
- `\humhub\modules\user\Module::enableRegistrationFormCaptcha` which is true by default (can be disabled via [file configuration](https://docs.humhub.org/docs/admin/advanced-configuration#module-configurations))
- `\humhub\modules\user\Module::$passwordHint` (see [#5423](https://github.com/humhub/humhub/issues/5423))
- New methods in the `DeviceDetectorHelper` class: `isMobile()`, `isTablet()`, `getBodyClasses()`, `isMultiInstanceApp()` and `appOpenerState()`
- HTML classes about the current device (see list in `DeviceDetectorHelper::getBodyClasses()`)

### Deprecated
- `\humhub\modules\ui\menu\MenuEntry::isActiveState()` use `\humhub\helpers\ControllerHelper::isActivePath()` instead
- `\humhub\modules\content\Module::$adminCanViewAllContent` and `\humhub\modules\content\Module::adminCanEditAllContent` use `\humhub\modules\admin\Module::$enableManageAllContentPermission` instead which enables the "Manage All Content" Group Permission
- `\humhub\modules\user\models\User::canViewAllContent()` use `\humhub\modules\user\models\User::canManageAllContent()` instead

### Removed
- `Include captcha in registration form` checkbox removed from "Administration" -> "Users" -> "Settings"
- Removed obsolete property `\humhub\modules\content\widgets\richtext\AbstractRichText::$record`
- Removed `\humhub\widgets\ShowMorePager` widget

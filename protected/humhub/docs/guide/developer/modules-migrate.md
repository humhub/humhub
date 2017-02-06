# Module Migration Guide

Here you will learn how you can adapt existing modules to working fine with actually versions.

## Migrate from 1.1 to 1.2

### Stream / Content Changes

The models WallEntry and Wall were removed. So all corresponding methods like getFirstWallEntryId() are not longer available.
The stream handling is now handled directly by the Content model. Also all stream classes (widgets, actions) are moved into the humhub\modules\stream package.



### File module changes

Please refer the new [File Handling](files.md) documentation section for more details regarding the new file management API.

- Deprecated widgets:
    - humhub\modules\user\widgets\UserPicker (replaced with humhub\modules\user\widgets\UserPickerField)
    - humhub\modules\space\widgets\Picker (replaced with humhub\modules\space\widgets\SpackePickerField)
    - humhub\widgets\DataSaved (replaced with humhub\components\View::saved)
- Removed Content models 'attachFileGuidsAfterSave' attribute and handling
- Deprecated File model methods
    - \humhub\modules\file\models\File::attachPrecreated
	- \humhub\modules\file\models\File::getFilesOfObject
	- \humhub\modules\file\models\File::getStoredFilePath
	- \humhub\modules\file\models\File::getPreviewImageUrl
	- \humhub\modules\file\models\File::attachPrecreated
	- \humhub\modules\file\models\File::getFilename
	- \humhub\modules\file\models\File::getInfoArray
	- \humhub\modules\file\models\File::getMimeBaseType
	- \humhub\modules\file\models\File::getMimeSubType
	- \humhub\modules\file\models\File::getExtension
- Removed configuration option 'showFilesWidgetBlacklist' use WallEntry showFiles attribute instead.
- File models title attributes is not longer automatically populated with the filename when empty
- Moved file upload capabilities (UploadedFile) from File model to FileUpload model
- Moved file store content by attribute capabilities from File model to FileContent model
- Created UploadAction/DownloadAction classes

### Javascript API changes

TBD

#### Pjax + TopNavigation:
Use

public $topMenuRoute = '/dashboard/dashboard';

within your controller for pjax topmenu support.

### Asset Handling changes

TBD


## Migrate from 1.0 to 1.1

- Dropped unused space attribute "website"

- ContentContainer Model Changes
    - Removed canWrite method (now requires own implementation using permissions)

- Content Model Changes
    - Removed space_id / user_id columns - added contentcontainer_id
    - Not longer validates content visibility (private/public) permissions

- system_admin attribute in user table was removed
 see [[humhub\modules\user\models\User::isSystemAdmin]]

- Renamed space header settings menu dropdown class
  from  [[humhub\modules\space\modules\manage\widgets\Menu]] to [[humhub\modules\space\widgets\HeaderControlsMenu]]

- Refactored settings system. see [Settings Documentation](modules-settings.md) for more details.
  Old settings api is still available in 1.1.x 

- Refactored user group system

- New administration menu structure

## Migrate from 0.20 to 1.0


## Migrate from 0.12 to 0.20

**Important: This release upgrades from Yii1 to Yii2 Framework!**

This requires an extensive migration of all custom modules/themes.
Find more details here: [HumHub 0.20 Migration](modules-migrate-0.20.md)

## Migrate from 0.11 to 0.12

- Rewritten Search 

## Migrate from 0.10 to 0.11

No breaking changes.

- Now handle ContentContainerController layouts, new option showSidebar
- New ContentAddonController Class
- New Wiki Parser / Editor Widget
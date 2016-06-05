# Module Migration Guide

Here you will learn how you can adapt existing modules to working fine with actually versions.

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

- Refactored settings system. see [Settings Documentation](dev-settings.md) for more details.
  Old settings api is still available in 1.1.x 

- Refactored user group system

- New administration menu structure

## Migrate from 0.20 to 1.0


## Migrate from 0.12 to 0.20

**Important: This release upgrades from Yii1 to Yii2 Framework!**

This requires an extensive migration of all custom modules/themes.
Find more details here: [HumHub 0.20 Migration](dev-migrate-0.20.md)

## Migrate from 0.11 to 0.12

- Rewritten Search 

## Migrate from 0.10 to 0.11

No breaking changes.

- Now handle ContentContainerController layouts, new option showSidebar
- New ContentAddonController Class
- New Wiki Parser / Editor Widget
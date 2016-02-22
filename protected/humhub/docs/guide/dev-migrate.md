# Module Migration Guide

Here you will learn how you can adapt existing modules to working fine with actually versions.

## to 1.1

- ContentContainer Model Changes
    - Removed canWrite method (now requires own implementation using permissions)

- Content Model Changes
    - Removed space_id / user_id columns - added contentcontainer_id
    - Not longer validates content visibility (private/public) permissions

## to 0.20

**Important: This release upgrades from Yii1 to Yii2 Framework!**

This requires an extensive migration of all custom modules/themes.
Find more details here: [HumHub 0.20 Migration](dev-migrate-0.20.md)

## to 0.12

- Rewritten Search 

## to 0.11

No breaking changes.

- Now handle ContentContainerController layouts, new option showSidebar
- New ContentAddonController Class
- New Wiki Parser / Editor Widget

## to 0.10

No breaking changes

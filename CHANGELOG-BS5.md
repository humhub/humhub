HumHub Changelog
================

Bootstrap 5 migration
------------

Modules and themes must be migrated to Bootstrap 5.
See MIGRATE_DEV-BS5.md

Migration proccess:
- The default HumHub theme will be activated
- Themes in the `themes` folder will be renamed by adding `.bs3.old`. 
- The Theme Builder module will be uninstalled.

- Enh: Upgrade Bootstrap library (Bootstrap 3 v2.0.0 => Bootstrap 5 v2.0)
- Enh: Upgrade Select2 Bootstrap Theme (Bootstrap 3 v0.1.0-beta.4 => Bootstrap v5 1.3.0)
- Enh: Upgrade jQuery library (3.6.4 => 3.7.1)
- Enh: Add [scssphp library](https://github.com/scssphp/scssphp) allowing to compile SCSS to CSS with PHP
- Enh: Add CSS compiler after saving the Appearance settings form and after flushing cache
- Enh: Add Appearance settings form fields: 8 main Bootstrap color, and a free SCSS textArea

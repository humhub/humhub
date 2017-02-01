Updating
========

> Warning: Before you run an update please check, if your installed modules and themes are compatible with your target version. If not, you can follow the migration guides.
- [Theme Migration Guide](theming-migrate.md)
- [Module Migration Guide](dev-migrate.md)

> NOTE: Only use this guide if you want to update from HumHub 0.20 or higher!
> If you want to update from an older version (e.g. 0.11.2 or lower) to HumHub 0.20+, you have to use this guide: **[Upgrade to 0.20 and above](admin-updating-020.md "Guide: Upgrade to 0.20 and above")**

> NOTE: Backup your data:
- Backup the whole HumHub installation folder from your webroot
- Make a complete MySQL Backup from your HumHub database


## Download Package installations

### Option 1: Updater Module

1. Administration -> Modules -> Browse online
2. Choose **HumHub Updater** and click 'Install'
3. Switch to 'Installed' Tab
4. Choose **HumHub Updater** and click 'Enable'
5. Select in left navigation: Administration -> Update HumHub
6. Follow the updater steps

### Option 2: Manual Update

1. Delete your current HumHub installation (Don't forget to make a backup as mentioned above, you will need these files later!)
2. Download the latest HumHub package from [http://www.humhub.org/downloads](http://www.humhub.org/downloads) and extract the package to your webroot
3. Restore the following files from backup:
	- /uploads/*
	- /protected/runtime
	- /protected/config/*
	- /protected/modules/* (if any)
	- /themes (if there are any custom themes - except HumHub default theme)
4. Run database migration tool

```
cd protected
php yii migrate/up --includeModuleMigrations=1
```




## Git/Composer based installations

- Pull latest Git version

```
git pull
```
- Update composer dependencies

```
composer update
```

- Run database migration tool

```
cd protected
php yii migrate/up --includeModuleMigrations=1
```



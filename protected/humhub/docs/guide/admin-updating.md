Updating
========

> NOTE: Only use this guide if you want to update from HumHub 0.20 to a higher version!
> If you want to update from 0.11.2 or lower to HumHub 0.20, you have to use this guide: **[Upgrade to 0.20 and above](admin-updating-020.md "Guide: Upgrade to 0.20 and above")**

1. Before you run an update please check, if your installed modules and themes are compatible with your targeted version. If not, you can follow the [Theme Migration Guide](theming-migrate.md) and [Module Migration Guide](dev-migrate.md) to make everything ready for the new version.

2. Backup your data:
	- Backup the whole HumHub installation folder from your webroot
	- Make a complete MySQL-Dump from your HumHub database

## Update with Git/Composer

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
php yii migrate/up
```


## Manuel update (Download Package)

1. Delete your current HumHub installation (Don't forget to make a backup as mentioned above, you will need these files later!)
2. Download the latest HumHub package from [http://www.humhub.org/downloads](http://www.humhub.org/downloads) and extract the package to your webroot
3. Restore the following files from backup:
	- /uploads/*
	- /protected/runtime
	- /protected/config/*
	- /protected/modules/* (if any)
	- /themes (if any) 
4. Run database migration tool

```
cd protected
php yii migrate/up
```

## Via Updater Module 

TBD

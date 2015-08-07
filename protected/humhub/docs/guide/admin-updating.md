Updating
========

- **This guide only affects updates from version 0.20**
	- **[Upgrade to 0.20](admin-updating-020.md "Guide: Upgrade to 0.20 and above")**
- Check custom module/theme compatiblity:
	- [Theme Migration](theming-migrate.md)
	- [Module Migration](dev-migrate.md)
- Backup your data
	- all files
	- database

## Via Git/Composer

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


## Via Download Package

- First, backup ALL your files & database
- Download package (http://www.humhub.org/downloads)
- Delete current installation files (Backup? :-))
- Extract download package
- Restore from backup:
	- /uploads/*
	- /protected/runtime
	- /protected/config/*
	- /protected/modules/* (if any)
	- /themes (if any) 
- Run database migration tool

```
cd protected
php yii migrate/up
```

## Via Updater Module 

TBD

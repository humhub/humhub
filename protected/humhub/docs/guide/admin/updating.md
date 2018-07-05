Updating
========
> Warning: Please check before you run an update, that your installed modules and themes are compatible with the new version. If not, you can follow the migration guides.
> - [Theme Migration Guide](../theme/migrate.md)
> - [Module Migration Guide](../developer/migration-guide.md)
> 
> Additional update notes:
> - [Update from 1.2 or below](updating-130.md)
> - [Update from 0.20 or below](updating-020.md)


> NOTE: Backup always your data before updating! See: [Backup Chapter](backup.md)

1. Delete your current HumHub installation (Don't forget to make a backup as mentioned above, you will need these files later!)
2. Download the latest HumHub package from [https://www.humhub.org/download](https://www.humhub.org/download) and extract the package to your webroot
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




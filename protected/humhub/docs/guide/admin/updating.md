Updating
========

> Warning: Please check before you run an update, that your installed modules and themes are compatible with the new version. If not, you can follow the migration guides.
- [Theme Migration Guide](../theme/migrate.md)
- [Module Migration Guide](../developer/modules-migrate.md)


> NOTE: Only use this guide if you want to update from HumHub 0.20 or higher!
> If you want to update from an older version (e.g. 0.11.2 or lower) to HumHub 0.20+, you have to use this guide: **[Upgrade to 0.20 and above](admin-updating-020.md "Guide: Upgrade to 0.20 and above")**

> NOTE: Backup your data before updating! See: [Backup Chapter](backup.md)

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




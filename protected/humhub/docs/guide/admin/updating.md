Manual Update
========

> Warning: Prior to an HumHub update, please check the compatibility of your installed custom modules and theme with the
new version.
For each version there will be a themeing and module migration guide available:
- [Theme Migration Guide](../theme/migrate.md)
- [Module Migration Guide](../developer/modules-migrate.md)
 
> Additional update notes for older versions:
> - [Update from 1.2 or below](updating-130.md)
> - [Update from 0.20 or below](updating-020.md)


> Warning: Always backup your data before updating! See: [Backup Chapter](backup.md)

1. Delete your current HumHub installation (Don't forget to make a backup as mentioned above, you will need these files later!)
2. Download the latest HumHub package from [https://www.humhub.org/download](https://www.humhub.org/download) and extract the package to your web-root
3. Restore the following files from backup:

	- /uploads/*
	- /protected/runtime
	- /protected/config/*
	- /protected/modules/* (if any)
	- /themes (if there are any custom themes - except HumHub default theme)
	
4. Run database migration tool

> Note: After a manual update you should also check for available module updates under `Administration -> Modules -> Avilable Updates`.

```
cd protected
php yii migrate/up --includeModuleMigrations=1
```

5. Update installed marketplace modules

```
cd protected
php yii module/update-all
```




Updating to 0.20
================

> NOTE: This guide only affects updates from HumHub 0.11.2 or lower to HumHub 0.20

1. Before you run an update please check, if your installed modules and themes are compatible with your targeted version. If not, you can follow the [Theme Migration Guide](theming-migrate.md) and [Module Migration Guide](dev-migrate.md) to make everything ready for the new version.

2. Backup your data:
	- Backup the whole HumHub installation folder from your webroot
	- Make a complete MySQL-Dump from your HumHub database

## Migration Steps

1. Delete your current HumHub installation (Don't forget to make a backup as mentioned above, you will need these files later!)
2. Download the latest HumHub package directly from [http://www.humhub.org/downloads](http://www.humhub.org/downloads) and extract it to your webroot or install it via [GitHub/Composer](admin-installation.md).
3. **IMPORTANT**: Before starting the Web installer you have to restore the /uploads/ directory form your backup to your new installation
4. Start the Web installer (e.g. [http://localhost/humhub](http://localhost/humhub)) and follow the instructions. If you enter the database name from your previous installation, HumHub will automatically migrate your existing database to the new version
5. Reinstall all previously installed modules/themes
  (Make sure to use a 0.20 compatible version!)
6. Rebuild the Search Index 

```
php yii search/rebuild
```


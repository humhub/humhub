Extended Migration Command
--------------------------

This extension is an enhanced version of the [Yii Database Migration Tool](http://www.yiiframework.com/doc/guide/1.1/en/database.migration)
that adds module support and many more usefull features. If there is anything you would like to have added, or you found a bug
please [report it](https://github.com/yiiext/migrate-command/issues) on github or [contact me](mailto:mail@cebe.cc) via email.

Features
--------

* Module-Support (migrations are distributed in a seperate folder for every module) so you can...
 * ...enable and disable Modules
 * ...[add](#hh8) new module by migrating it up
 * ...[remove](#hh9) a module by running its migrations down
 * ...[select the modules](#hh7) you want to run migrations for in every run
 * ...declare Module-dependencies (coming soon)
 * ...different migration templates depending on modules (coming soon)

Resources
---------

* [GIT](https://github.com/yiiext/migrate-command)
* [Yii Database Migration Documentation](http://www.yiiframework.com/doc/guide/1.1/en/database.migration)
* [Discuss](http://www.yiiframework.com/forum/index.php?/topic/22471-extension-extended-database-migration/)
* [Report a bug/request feature](https://github.com/yiiext/migrate-command/issues)

Requirements
------------

* Yii 1.1.6 or above (MigrateCommand was introduced in this version)
if you copy MigrateCommand and [CDbMigration](http://www.yiiframework.com/doc/api/1.1/CDbMigration) you should be able to use this
extension with any yii version.

Installation
------------

* Extract the release file under `protected/extensions`.
* Add the following to your [config file](http://www.yiiframework.com/doc/guide/1.1/en/database.migration#customizing-migration-command) for yiic command:

```php
'commandMap' => array(
	'migrate' => array(
		// alias of the path where you extracted the zip file
		'class' => 'application.extensions.yiiext.commands.migrate.EMigrateCommand',
		// this is the path where you want your core application migrations to be created
		'migrationPath' => 'application.db.migrations',
		// the name of the table created in your database to save versioning information
		'migrationTable' => 'tbl_migration',
		// the application migrations are in a pseudo-module called "core" by default
		'applicationModuleName' => 'core',
		// define all available modules (if you do not set this, modules will be set from yii app config)
		'modulePaths' => array(
			'admin'      => 'application.modules.admin.db.migrations',
			'user'       => 'application.modules.user.db.migrations',
			'yourModule' => 'application.any.other.path.possible',
			// ...
		),
		// you can customize the modules migrations subdirectory which is used when you are using yii module config
		'migrationSubPath' => 'migrations',
		// here you can configure which modules should be active, you can disable a module by adding its name to this array
		'disabledModules' => array(
			'admin', 'anOtherModule', // ...
		),
		// the name of the application component that should be used to connect to the database
		'connectionID'=>'db',
		// alias of the template file used to create new migrations
		'templateFile'=>'application.db.migration_template',
	),
),
```

**Please note:** if you already used MigrateCommand before, make sure to add the module column to your migrationTable:

```sql
ALTER TABLE `tbl_migration` ADD COLUMN `module` varchar(32) DEFAULT NULL;
UPDATE `tbl_migration` SET module='core';
```

Usage
-----

###General Usage

You can run `yiic migrate help` to see all parameters and a short example on how to use them.

The basics are explained in the [Definitive Guide to Yii](http://www.yiiframework.com/doc/guide/1.1/en/database.migration). Read them first if you haven't used database migration before.
The usage of Extended Migration Command is not much different from the native one.
The only command that is different is [create](http://www.yiiframework.com/doc/guide/1.1/en/database.migration#creating-migrations) where you have to additionally specify the modulename:

```
yiic migrate create modulename create_user_table
```

This creates a new migration named 'create_user_table' in module 'modulename'. The native usage

```
yiic migrate create create_user_table
```

creates a new migration named 'create_user_table' in the application(core).

###--module Parameter

In all other commands (`up`, `down`, `history`, `new`, `to` and `mark`) you can use the parameter `--module=<modulenames>` where `<modulenames>` can be a comma seperated list of module names or a single module name. This parameter will limit the current command to affect only the specified modules.
Some Examples:

```
yiic migrate new --module=core
```

This will show you all new migrations for module core and

```
yiic migrate up 5 --module=core,user
```

will migrate up the next 5 new migrations in modules core and user. If there are new migrations in other modules they will be ignored.

```
yiic migrate history --module=core,user
```

will show you which migrations have been applied for modules core and user in the past.
If you do not specify a module the command behaves like the native one and does the migration for all modules.

###add a module

Simply enable it in your config and run `yiic migrate up --module=yourModule`.

###remove a module

Run `yiic migrate to m000000_000000 --module=yourModule`. For this to work all your migrations must have the down()-method implemented correctly.

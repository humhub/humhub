Database
========

Use migrations to create or update your database tables.
See http://www.yiiframework.com/doc/guide/1.1/en/database.migration for all basics about migrations.

If you need own tables, please use a prefix "mymodule_" on table name.


Create a Migration:

* Create a migrations folder in your module (e.g. protected/modules/example/migrations)
* Create a migration with: ``php yiic.php migrate create mymoduleid migrationname``

Execute Migrations:

* Execute migrations with ``php yiic.php migrate``







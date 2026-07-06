# Database Models

This guide describes how to setup and update your modules database scheme. All database installation and update scripts will reside in
your modules `migration` directory.

## Conventions

- **prefix** your tables with the module id, e.g. `example_foo`
- **singular** table names
- **snake_case** field and attribute names, e.g. `user_id`

## Initial Migration

Once you've a concept of your database scheme ready and want to start prototyping, you'll need to create an initial [migration](https://www.yiiframework.com/doc/guide/2.0/en/db-migrations#creating-migrations).

The following command executed in the `protected` directory will create a new migration into the `protected/humhub/migrations` folder:

```
php yii migrate/create mymodule_inital
```

which resembles the following grunt command executed within the `root` of your installation:

```
grunt migrate-create --name=mymodule_inital
```

Just copy the resulting migration from the `protected/humhub/migrations` folder into your module's `migrations` folder and add your schema setup.
Please refer to the [Yii Migration Guide](https://www.yiiframework.com/doc/guide/2.0/en/db-migrations#creating-migrations) for more information about
how to use migrations.

Your table names should be prefixed with your unique module id like `mymodule_entry`.

> Info: The `humhub\components\Migration` class provides some additional helper methods like  `safeCreateTable()`, `safeAddColumn()`, etc. which check if the table or column exists before creating or adding it.

> Tip: Since a `Migration::safeUp()` uses transactions you should consider splitting your migration files into multiple migrations.

> Note: Only data manipulation queries can be rolled back in case a migration script fails.

## Scheme Updates

In order to provide scheme updates for new versions, just follow the same steps as in the [Initial Migration](#initial-migration) section.

You can manually execute new migrations by the following commands:

```
php yii migrate/up --includeModuleMigrations=1
```

or by grunt 

```
grunt migrate-up
```

Missing migrations are also executed when accessing `Administration -> Information -> Database`.

## Migrations and module context

Migrations run with your module **registered**: the namespace alias from your
`config.php` is set (so your module's classes are autoloadable, even when the
module id differs from the namespace), and `Yii::$app->getModule('<id>')`
returns your module instance — settings access works. This applies to all
migration entry points alike: `migrate/up` on the console, the web-based
migration (`Administration -> Information -> Database`), module activation and
`marketplace/update-all`.

One caveat: when the console migration scan cannot **register** your module —
typically during a core upgrade, while the installed module version still
references core classes that were removed — the module is skipped with a
warning and its migrations are deferred until the module itself is updated.
Migrations of disabled modules never run; they are applied when the module is
enabled.

To keep migrations robust across all situations:

- Prefer plain DB operations over reaching through services where practical —
  e.g. write settings directly to the `setting` table instead of
  `Yii::$app->getModule('<id>')->settings` when the migration is part of an
  upgrade path that may run against a newer core:

  ```php
  $this->upsert('setting',
      ['module_id' => 'mymodule', 'name' => 'foo', 'value' => $value],
      ['value' => $value]);
  ```

- Never assume classes of *other* modules are available in your migration.

Your `uninstall.php` migration runs while your module is still registered.
`Module::disable()` already clears your module's global and container settings
(see [Settings](concept-settings.md)), so an uninstall migration only needs to
drop tables and columns.

## Uninstall Migration

Your module should also provide an `uninstall.php` file.
The uninstall migration is by default executed within your `Module::disable()` logic method and should look like:

```
use yii\db\Migration;

class uninstall extends Migration
{

    public function up()
    {
        $this->dropTable('mymodule_entry');
        $this->dropTable('mymodule_entry_user');
    }

    public function down()
    {
        echo "uninstall does not support migration down.\n";
        return false;
    }

}
```

## Integrity Check

The integrity check is a command which validates and if necessary repairs the application database.

If you want to add own checking methods for your module to it, you can intercept the `humhub\commands\IntegrityController::EVENT_ON_RUN` event.

Example callback implementation:

```php
public static function onIntegrityCheck($event)
{
    $integrityController = $event->sender;
    $integrityController->showTestHeadline("Polls Module - Answers (" . PollAnswer::find()->count() . " entries)");

    foreach (PollAnswer::find()->joinWith('poll')->all() as $answer) {
        if ($answer->poll === null) {
            if ($integrityController->showFix("Deleting poll answer id " . $answer->id . " without existing poll!")) {
                $answer->delete();
            }
        }
    }
}
```

## ActiveRecord

HumHub uses Yii's [ActiveRecords](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record) as database access layer. The base class `humhub\components\ActiveRecord` extends `yii\db\ActiveRecord` and automatically maintains the columns `created_by`, `created_at`, `updated_by`, `updated_at` when they exist on the table.

For polymorphic relations, store the target as a `humhub\modules\content\models\Content` row and use [`PolymorphicRelation`](https://github.com/humhub/humhub/blob/master/protected/humhub/libs/PolymorphicRelation.php) — never store class names directly. The base `Migration` class includes a `renameClass()` helper because polymorphic relations are stored as fully-qualified class names; renaming a class is therefore a migration concern.

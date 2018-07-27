Module Database Scheme
=================

This guide describes how to setup and update your modules database scheme. All database installation and update scripts will reside in
your modules `migration` directory.

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

Just copy the resulting migration from the `protected/humhub/migrations` into your modules `migration` folder and add your scheme setup.
Please refer to the [Yii Migration Guide](https://www.yiiframework.com/doc/guide/2.0/en/db-migrations#creating-migrations) for more information about
how to use the `Migration` guide.

Your table names should be prefixed with your unique module id like `mymodule_entry`.

> Info: The [[humhub\components\Migration]] class provides some additional helper functions.

> Tip: Since a `Migration::safeUp()` uses transactions you should consider splitting your migration files into multiple migrations.

> Note: Only data manipulation queries can be rolled back in case a migration script fails.

## Scheme Updates

In order to provide scheme updates for new versions, just follow the same steps as in the [Initial Migration](#initial-migration) section.

You can manually execute new migrations by the following commands:

```
php yii migrate/up --inclueModuleMigrations=1
```

or grunt 

```
grunt migrate-up
```

Missing migrations are also executed when accessing `Administration -> Information -> Database`.

## Uninstall Migration

Your module should also privde an `uninstall.php` file.
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
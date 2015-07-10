Database
========

### Conventions

- prefix your tables with the module id. e.g. example_foo
- singular table names
- use underscorce in fieldnames/attributes e.g. user_id

### ActiveRecord

TBD

[[humhub\components\ActiveRecord]]


### Migrations

See Yii 2.0 guide for more details about migrations [http://www.yiiframework.com/doc-2.0/guide-db-migrations.html](http://www.yiiframework.com/doc-2.0/guide-db-migrations.html).

HumHub provides an enhanced Migration class [[humhub\components\Migration]] which provides the ability to rename class files. This is required because HumHub also stores some class names in database for Polymorphic relations.


** Examples: **


- Create a module migration
	> php yii migrate/create example --migrationPath='@app/modules/polls/migrations'

- Execute module migrations
	> php yii migrate/up --migrationPath='@app/modules/polls/migrations'

- Execute all migrations (including enabled modules)
	> php yii migrate/up --includeModuleMigrations=1

#### Uninstall

There is a special migration file called 'uninstall.php' - which is executed after the module is uninstalled.
Use this drop created tables & columns.

Example file: *migrations/uninstall.php*

```php
<?php

use yii\db\Migration;

class uninstall extends Migration
{

    public function up()
    {

        $this->dropTable('poll');
        $this->dropTable('poll_answer');
        $this->dropTable('poll_answer_user');
    }

    public function down()
    {
        echo "uninstall does not support migration down.\n";
        return false;
    }

}
```
Database and Models
====================

## Conventions

- prefix your tables with the module id. e.g. example_foo
- singular table names
- use underscorce in fieldnames/attributes e.g. user_id

## ActiveRecord (Model)

To be able to provide persistent data a module has to implement model class derived from [[humhub\components\ActiveRecord]].
Yii follows the concept of rich models, which means a model class can contain content in form of attributes as well as domain logic.
More information about the use of ActiveRecords is available in the [Yii2 guide](http://www.yiiframework.com/doc-2.0/guide-db-active-record.html).

> Info: [[humhub\components\ActiveRecord]] is derived from [[yii\db\ActiveRecord]] and provides some automatic attribute settings as `created_by` and `crated_at` if the underlying table contains these fields.

## Migrations

See Yii 2.0 guide for more details about migrations [http://www.yiiframework.com/doc-2.0/guide-db-migrations.html](http://www.yiiframework.com/doc-2.0/guide-db-migrations.html).

HumHub provides an enhanced Migration class [[humhub\components\Migration]] which provides the ability to rename class files. This is required because HumHub also stores some class names in database for Polymorphic relations.

#### Usage

- Create a module migration
	`> php yii migrate/create example --migrationPath='@app/modules/polls/migrations'`
- Execute module migration
	`> php yii migrate/up --migrationPath='@app/modules/polls/migrations'`
- Execute all migrations (including enabled modules)
	`> php yii migrate/up --includeModuleMigrations=1`

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

## Integrity Checker

The integrity checker is a command which validates and if necessary repairs the application database.

If you want to add own checking methods for your module to it, you can intercept the [[humhub\controllers\IntegrityController::EVENT_ON_RUN]] event.

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
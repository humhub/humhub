<?php

use humhub\components\Migration;
use Some\Name\Space\mismatch\Module;

/**
 * References a class from the module namespace, like real-world module migrations do
 * (e.g. auth-keycloak's m260515_120000_backfill_user_source). Executing this migration
 * requires the module's namespace alias to be registered.
 */
class m260101_000000_probe extends Migration
{
    public function safeUp()
    {
        // Class constant access forces autoloading of the module class.
        Yii::debug('Probe migration for module ' . Module::ID);
    }

    public function safeDown()
    {
    }
}

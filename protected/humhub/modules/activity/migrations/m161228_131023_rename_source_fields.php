<?php

use yii\db\Migration;

class m161228_131023_rename_source_fields extends Migration
{
    public function up()
    {
        $this->renameColumn('activity', 'object_model', 'source_class');
        $this->renameColumn('activity', 'object_id', 'source_pk');
    }

    public function down()
    {
        echo "m161228_131023_rename_source_fields cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}

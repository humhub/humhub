<?php

use yii\db\Migration;

class m160408_100725_rename_groupadmin_to_manager extends Migration
{
    public function up()
    {
           $this->renameColumn('group_user', 'is_group_admin', 'is_group_manager');
    }

    public function down()
    {
        echo "m160408_100725_rename_groupadmin_to_manager cannot be reverted.\n";

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

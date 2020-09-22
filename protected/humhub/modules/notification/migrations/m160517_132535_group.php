<?php

use yii\db\Migration;

class m160517_132535_group extends Migration
{
    public function up()
    {
        $this->addColumn('notification', 'group_key', $this->string(75));
        $this->createIndex('index_groupuser', 'notification', ['user_id', 'class', 'group_key']);
    }

    public function down()
    {
        echo "m160517_132534_group cannot be reverted.\n";

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

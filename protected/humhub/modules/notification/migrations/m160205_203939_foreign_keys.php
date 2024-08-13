<?php

use yii\db\Schema;
use yii\db\Migration;

class m160205_203939_foreign_keys extends Migration
{
    public function up()
    {
        $this->addForeignKey('fk_notification-user_id', 'notification', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_notification-user_id', 'notification');

        return true;
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

<?php

use yii\db\Schema;
use yii\db\Migration;

class m160205_203933_foreign_keys extends Migration
{
    public function up()
    {
        $this->addForeignKey('fk_post-created_by', 'post', 'created_by', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_post-created_by', 'post');

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

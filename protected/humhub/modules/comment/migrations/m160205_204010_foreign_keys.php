<?php

use yii\db\Schema;
use yii\db\Migration;

class m160205_204010_foreign_keys extends Migration
{
    public function up()
    {
        $this->addForeignKey('fk_comment-created_by', 'comment', 'created_by', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_comment-created_by', 'comment');

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

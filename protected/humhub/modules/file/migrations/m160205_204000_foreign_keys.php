<?php

use yii\db\Schema;
use yii\db\Migration;

class m160205_204000_foreign_keys extends Migration
{
    public function up()
    {
        $this->addForeignKey('fk_file-created_by', 'file', 'created_by', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_file-created_by', 'file');

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

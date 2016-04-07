<?php

use yii\db\Schema;
use yii\db\Migration;

class m160205_205540_foreign_keys extends Migration
{
    public function up()
    {
        $this->addForeignKey('fk_content-user_id', 'content', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_wall_entry-wall_id', 'wall_entry', 'wall_id', 'wall', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        //echo "m160205_205540_foreign_keys cannot be reverted.\n";

        $this->dropForeignKey('fk_content-user_id', 'content');
        $this->dropForeignKey('fk_wall_entry-wall_id', 'wall');
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

<?php

use yii\db\Schema;
use yii\db\Migration;

class m160205_205540_foreign_keys extends Migration
{
    public function up()
    {
        $this->addForeignKey('fk_wall_entry-wall_id', 'wall_entry', 'wall_id', 'wall', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
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

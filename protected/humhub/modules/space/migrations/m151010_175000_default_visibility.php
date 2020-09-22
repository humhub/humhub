<?php

use yii\db\Schema;
use yii\db\Migration;

class m151010_175000_default_visibility extends Migration
{
    public function up()
    {
        $this->addColumn('space', 'default_content_visibility', Schema::TYPE_BOOLEAN);
    }

    public function down()
    {
        echo "m151010_175000_default_visibility cannot be reverted.\n";

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

<?php

use yii\db\Migration;

class m170211_105743_show_in_stream extends Migration
{
    public function up()
    {
        $this->addColumn('file', 'show_in_stream', $this->boolean()->defaultValue(true));
    }

    public function down()
    {
        echo "m170211_105743_show_in_stream cannot be reverted.\n";

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

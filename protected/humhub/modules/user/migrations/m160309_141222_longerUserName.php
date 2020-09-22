<?php

use yii\db\Migration;

class m160309_141222_longerUserName extends Migration
{
    public function up()
    {
        $this->alterColumn('user', 'username', 'VARCHAR(50)');
    }

    public function down()
    {
        echo "m160309_141222_longerUserName cannot be reverted.\n";

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

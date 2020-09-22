<?php


use yii\db\Migration;

class m150703_012735_typelength extends Migration
{
    public function up()
    {
        $this->renameColumn('activity', 'type', 'class');
        $this->alterColumn('activity', 'class', 'varchar(100) NOT NULL');
    }

    public function down()
    {
        echo "m150703_012735_typelength cannot be reverted.\n";

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

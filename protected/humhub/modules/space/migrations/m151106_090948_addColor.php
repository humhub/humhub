<?php


use yii\db\Migration;

class m151106_090948_addColor extends Migration
{
    public function up()
    {
        $this->addColumn('space', 'color', 'varchar(7)');
    }

    public function down()
    {
        echo "m151106_090948_addColor cannot be reverted.\n";

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

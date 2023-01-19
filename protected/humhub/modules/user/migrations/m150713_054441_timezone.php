<?php


use yii\db\Migration;

class m150713_054441_timezone extends Migration
{

    public function up()
    {
        $this->addColumn('user', 'time_zone', 'VARCHAR(100) DEFAULT NULL');
    }

    public function down()
    {
        echo "m150713_054441_timezone cannot be reverted.\n";

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

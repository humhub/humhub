<?php

use yii\db\Migration;

class m160501_220850_activity_pk_int extends Migration
{

    public function up()
    {
        $this->alterColumn('activity', 'object_id', 'INT(11) NOT NULL');
    }

    public function down()
    {
        echo "m160501_220850_activity_pk_int cannot be reverted.\n";

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

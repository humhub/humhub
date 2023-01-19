<?php


use yii\db\Migration;

class m150302_114347_add_visibility extends Migration
{

    public function up()
    {
        $this->addColumn('user', 'visibility', 'INT(1) DEFAULT 1');
    }

    public function down()
    {
        echo "m150302_114347_add_visibility does not support migration down.\n";
        return false;
    }

    /*
      // Use safeUp/safeDown to do migration with transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}

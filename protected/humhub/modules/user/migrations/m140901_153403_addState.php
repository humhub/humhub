<?php


use yii\db\Migration;

class m140901_153403_addState extends Migration
{

    public function up()
    {
        $this->addColumn('user_module', 'state', 'int(4)');
        $this->dropColumn('user_module', 'created_at');
        $this->dropColumn('user_module', 'created_by');
        $this->dropColumn('user_module', 'updated_at');
        $this->dropColumn('user_module', 'updated_by');

        $this->update('user_module', array('state' => 1));
    }

    public function down()
    {
        echo "m140901_153403_addState does not support migration down.\n";
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

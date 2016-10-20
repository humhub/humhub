<?php


use yii\db\Migration;

class m140901_112246_addState extends Migration
{

    public function up()
    {
        $this->addColumn('space_module', 'state', 'int(4)');
        $this->dropColumn('space_module', 'created_at');
        $this->dropColumn('space_module', 'created_by');
        $this->dropColumn('space_module', 'updated_at');
        $this->dropColumn('space_module', 'updated_by');

        $this->update('space_module', array('state' => 1));
    }

    public function down()
    {
        echo "m140901_112246_addState does not support migration down.\n";
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

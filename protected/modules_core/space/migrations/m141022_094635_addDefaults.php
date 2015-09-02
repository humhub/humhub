<?php

class m141022_094635_addDefaults extends EDbMigration
{

    public function up()
    {
        $this->insert('setting', array('name'=>'defaultVisibility', 'module_id'=>'space', 'value'=>'1'));
        $this->insert('setting', array('name'=>'defaultJoinPolicy', 'module_id'=>'space', 'value'=>'1'));
    }

    public function down()
    {
        echo "m141022_094635_addDefaults does not support migration down.\n";
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

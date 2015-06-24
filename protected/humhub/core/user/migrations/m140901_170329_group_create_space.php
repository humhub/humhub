<?php

class m140901_170329_group_create_space extends EDbMigration
{

    public function up()
    {
        $this->addColumn('group', 'can_create_public_spaces', 'INT(1) DEFAULT 1');
        $this->addColumn('group', 'can_create_private_spaces', 'INT(1) DEFAULT 1');
    }

    public function down()
    {
        echo "m140901_170329_group_create_space does not support migration down.\n";
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

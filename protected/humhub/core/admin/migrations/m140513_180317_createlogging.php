<?php

class m140513_180317_createlogging extends EDbMigration
{

    public function up()
    {
        $this->createTable('logging', array(
            'id' => 'pk',
            'level' => 'varchar(128)',
            'category' => 'varchar(128)',
            'logtime' => 'integer',
            'message' => 'text',
                ), '');
    }

    public function down()
    {
        echo "m140513_180317_createlogging does not support migration down.\n";
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

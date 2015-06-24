<?php

class m140902_091234_session_key_length extends EDbMigration
{

    public function up()
    {
        $this->alterColumn('user_http_session', 'id', 'char(255) NOT NULL');
    }

    public function down()
    {
        echo "m140902_091234_session_key_length does not support migration down.\n";
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

<?php

class m140706_135210_lastlogin extends EDbMigration
{

    public function up()
    {
        $this->addColumn('user', 'last_login', 'DATETIME DEFAULT NULL');
    }

    public function down()
    {
        echo "m140706_135210_lastlogin does not support migration down.\n";
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

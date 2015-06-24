<?php

class m140930_210142_fix_default extends EDbMigration
{

    public function up()
    {
        $this->alterColumn('file', 'object_model', "varchar(100) DEFAULT ''");
        $this->alterColumn('file', 'object_id', "varchar(100) DEFAULT ''");
    }

    public function down()
    {
        echo "m140930_210142_fix_default does not support migration down.\n";
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

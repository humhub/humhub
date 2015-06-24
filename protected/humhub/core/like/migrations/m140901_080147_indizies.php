<?php

class m140901_080147_indizies extends EDbMigration
{

    public function up()
    {
        $this->createIndex('index_object', 'like', 'object_model, object_id', false);
    }

    public function down()
    {
        echo "m140901_080147_indizies does not support migration down.\n";
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

<?php


use humhub\components\Migration;

class m140930_205511_fix_default extends Migration
{
    public function up()
    {
        $this->safeAlterColumn('activity', 'module', "varchar(100) DEFAULT ''");
        $this->safeAlterColumn('activity', 'object_model', "varchar(100) DEFAULT ''");
        $this->safeAlterColumn('activity', 'object_id', "varchar(100) DEFAULT ''");
    }

    public function down()
    {
        echo "m140930_205511_fix_default does not support migration down.\n";
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

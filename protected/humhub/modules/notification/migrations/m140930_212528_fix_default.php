<?php

use humhub\components\Migration;

class m140930_212528_fix_default extends Migration
{
    public function up()
    {
        $this->safeAlterColumn('notification', 'emailed', 'tinyint(4) NOT NULL DEFAULT \'0\'');
        $this->safeAlterColumn('notification', 'updated_by', "int(11) DEFAULT NULL");
        $this->safeAlterColumn('notification', 'created_by', "int(11) DEFAULT NULL");
    }

    public function down()
    {
        echo "m140930_212528_fix_default does not support migration down.\n";
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

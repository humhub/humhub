<?php


use humhub\components\Migration;

class m141020_162639_fix_default extends Migration
{
    public function up()
    {

        $this->safeAlterColumn('setting', 'value', "varchar(255) DEFAULT NULL");
        $this->safeAlterColumn('setting', 'updated_by', "int(11) DEFAULT NULL");
        $this->safeAlterColumn('setting', 'created_by', "int(11) DEFAULT NULL");
        $this->safeAlterColumn('setting', 'created_at', "datetime DEFAULT NULL");
        $this->safeAlterColumn('setting', 'updated_at', "datetime DEFAULT NULL");
    }

    public function down()
    {
        echo "m141020_162639_fix_default does not support migration down.\n";
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

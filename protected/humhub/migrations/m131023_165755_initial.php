<?php

use yii\db\Schema;
use yii\db\Migration;

class m131023_165755_initial extends Migration
{

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('setting', array(
            'id' => 'pk',
            'name' => 'varchar(100) NOT NULL',
            'value' => 'varchar(255) NOT NULL',
            'value_text' => 'text DEFAULT NULL',
            'module_id' => 'varchar(100) DEFAULT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), $tableOptions);

        $this->createTable('module_enabled', array(
            'module_id' => 'varchar(100) NOT NULL',
                ), $tableOptions);

        $this->addPrimaryKey('pk_module_enabled', 'module_enabled', 'module_id');
    }

    public function down()
    {
        echo "m131023_165755_initial does not support migration down.\n";
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

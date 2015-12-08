<?php

use yii\db\Schema;
use yii\db\Migration;

class m131023_165411_initial extends Migration {

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('space', array(
            'id' => 'pk',
            'guid' => 'varchar(45) DEFAULT NULL',
            'wall_id' => 'int(11) DEFAULT NULL',
            'name' => 'varchar(45) NOT NULL',
            'description' => 'text DEFAULT NULL',
            'website' => 'varchar(45) DEFAULT NULL',
            'join_policy' => 'tinyint(4) DEFAULT NULL',
            'visibility' => 'tinyint(4) DEFAULT NULL',
            'status' => 'tinyint(4) NOT NULL DEFAULT \'1\'',
            'tags' => 'text DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), $tableOptions);

        $this->createTable('space_follow', array(
            'user_id' => 'int(11) NOT NULL',
            'space_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), $tableOptions);

        $this->addPrimaryKey('pk_space_follow', 'space_follow', 'user_id,space_id');

        $this->createTable('space_module', array(
            'id' => 'pk',
            'module_id' => 'varchar(255) NOT NULL',
            'space_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), $tableOptions);
    }

    public function down() {
        echo "m131023_165411_initial does not support migration down.\n";
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

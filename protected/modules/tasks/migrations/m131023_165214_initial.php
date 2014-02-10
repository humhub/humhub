<?php

class m131023_165214_initial extends ZDbMigration {

    public function up() {

        $this->createTable('task', array(
            'id' => 'pk',
            'title' => 'text NOT NULL',
            'deathline' => 'datetime DEFAULT NULL',
            'max_users' => 'int(11) NOT NULL',
            'status' => 'int(11) NOT NULL',
            'percent' => 'smallint(6) NOT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), '');

        $this->createTable('task_user', array(
            'id' => 'pk',
            'user_id' => 'int(11) NOT NULL',
            'task_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), '');
    }

    public function down() {
        echo "m131023_165214_initial does not support migration down.\n";
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
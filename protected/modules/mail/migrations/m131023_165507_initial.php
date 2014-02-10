<?php

class m131023_165507_initial extends ZDbMigration {

    public function up() {

        $this->createTable('user_message', array(
            'message_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'is_originator' => 'tinyint(4) DEFAULT NULL',
            'last_viewed' => 'datetime DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');
        $this->addPrimaryKey('pk_user_message', 'user_message', 'message_id,user_id');


        $this->createTable('message', array(
            'id' => 'pk',
            'title' => 'varchar(255) DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');

        $this->createTable('message_entry', array(
            'id' => 'pk',
            'message_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'file_id' => 'int(11) DEFAULT NULL',
            'content' => 'text NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');
    }

    public function down() {
        echo "m131023_165507_initial does not support migration down.\n";
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
<?php

class m140507_150421_create_settings_table extends EDbMigration {

    public function up() {

        // Create New User Settings Table
        $this->createTable('user_setting', array(
            'id' => 'pk',
            'user_id' => 'int(10)',
            'module_id' => 'varchar(100) DEFAULT NULL',
            'name' => 'varchar(100)',
            'value' => 'varchar(255) DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');

        $this->createIndex('idx_user_setting', 'user_setting', 'user_id, module_id, name', true);
    }

    public function down() {
        echo "m140507_150421_create_settings_table does not support migration down.\n";
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

<?php

class m131023_170253_initial extends ZDbMigration {

    public function up() {
        $this->createTable('comment', array(
            'id' => 'pk',
            'message' => 'text DEFAULT NULL',
            'object_model' => 'varchar(100) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'space_id' => 'int(11) DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');
    }

    public function down() {
        echo "m131023_170253_initial does not support migration down.\n";
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
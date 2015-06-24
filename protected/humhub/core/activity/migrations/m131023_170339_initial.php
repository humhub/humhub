<?php

class m131023_170339_initial extends EDbMigration {

    public function up() {
        $this->createTable('activity', array(
            'id' => 'pk',
            'type' => 'varchar(45) DEFAULT NULL',
            'module' => 'varchar(100) NOT NULL',
            'object_model' => 'varchar(100) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');
    }

    public function down() {
        echo "m131023_170339_initial does not support migration down.\n";
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
<?php

class m131023_165921_initial extends EDbMigration {

    public function up() {

        $this->createTable('poll', array(
            'id' => 'pk',
            'question' => 'varchar(255) NOT NULL',
            'allow_multiple' => 'tinyint(4) NOT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), '');

        $this->createTable('poll_answer', array(
            'id' => 'pk',
            'poll_id' => 'int(11) NOT NULL',
            'answer' => 'varchar(255) NOT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), '');

        $this->createTable('poll_answer_user', array(
            'id' => 'pk',
            'poll_id' => 'int(11) NOT NULL',
            'poll_answer_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), '');
    }

    public function down() {
        echo "m131023_165921_initial does not support migration down.\n";
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
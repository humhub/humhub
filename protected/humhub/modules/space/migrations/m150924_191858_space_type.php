<?php

use yii\db\Schema;
use yii\db\Migration;

class m150924_191858_space_type extends Migration
{

    public function up()
    {
        $this->createTable('space_type', array(
            'id' => Schema::TYPE_PK,
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'item_title' => Schema::TYPE_STRING . ' NOT NULL',
            'sort_key' => Schema::TYPE_INTEGER . ' DEFAULT 100 NOT NULL',
            'show_in_directory' => Schema::TYPE_BOOLEAN . ' DEFAULT 1 NOT NULL',
                ), '');

        $this->insert('space_type', [
            'id' => 1,
            'title' => 'Spaces',
            'item_title' => 'Space',
            'sort_key' => 100,
            'show_in_directory' => true,
        ]);

        $this->addColumn('space', 'space_type_id', Schema::TYPE_BIGINT);
        $this->update('space', ['space_type_id' => 1]);
    }

    public function down()
    {
        echo "m150924_191858_space_type cannot be reverted.\n";

        return false;
    }

    /*
      // Use safeUp/safeDown to run migration code within a transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}

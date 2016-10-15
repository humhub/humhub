<?php


use yii\db\Migration;

class m131023_165835_initial extends Migration
{

    public function up()
    {

        $this->createTable('post', array(
            'id' => 'pk',
            'message' => 'text DEFAULT NULL',
            'original_message' => 'text DEFAULT NULL',
            'url' => 'varchar(255) DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');
    }

    public function down()
    {
        echo "m131023_165835_initial does not support migration down.\n";
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

<?php


use yii\db\Migration;

class m131023_165625_initial extends Migration
{

    public function up()
    {

        $this->createTable('wall', array(
            'id' => 'pk',
            'type' => 'varchar(45) DEFAULT NULL',
            'object_model' => 'varchar(50) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');

        $this->createTable('wall_entry', array(
            'id' => 'pk',
            'wall_id' => 'int(11) NOT NULL',
            'content_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');


        $this->createTable('content', array(
            'id' => 'pk',
            'guid' => 'varchar(45) NOT NULL',
            'object_model' => 'varchar(100) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'visibility' => 'tinyint(4) DEFAULT NULL',
            'sticked' => 'tinyint(4) DEFAULT NULL',
            'archived' => 'tinytext DEFAULT NULL',
            'space_id' => 'int(11) DEFAULT NULL',
            'user_id' => 'int(11) DEFAULT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), '');
    }

    public function down()
    {
        echo "m131023_165625_initial does not support migration down.\n";
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

<?php

use yii\db\Schema;
use yii\db\Migration;

class m140513_180317_createlogging extends Migration
{

    public function up()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'ENGINE=InnoDB';
        }

        $this->createTable('logging',
            array(
            'id' => 'pk',
            'level' => 'varchar(128)',
            'category' => 'varchar(128)',
            'logtime' => 'integer',
            'message' => 'text',
            ), $tableOptions);
    }

    public function down()
    {
        echo "m140513_180317_createlogging does not support migration down.\n";
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
<?php

use yii\db\Schema;
use yii\db\Migration;

class m131023_170135_initial extends Migration {

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('like', array(
            'id' => 'pk',
            'target_user_id' => 'int(11) NOT NULL',
            'object_model' => 'varchar(100) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'created_at' => 'datetime DEFAULT NULL',
            'created_by' => 'int(11) DEFAULT NULL',
            'updated_at' => 'datetime DEFAULT NULL',
            'updated_by' => 'int(11) DEFAULT NULL',
                ), $tableOptions);
    }

    public function down()
    {
        echo "m131023_170135_initial does not support migration down.\n";
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

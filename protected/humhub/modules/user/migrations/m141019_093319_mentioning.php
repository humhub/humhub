<?php


use yii\db\Migration;

class m141019_093319_mentioning extends Migration
{

    public function up()
    {
        $this->createTable('user_mentioning', array(
            'id' => 'pk',
            'object_model' => 'varchar(100) NOT NULL',
            'object_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
                ), '');

        $this->createIndex('i_user', 'user_mentioning', 'user_id', false);
        $this->createIndex('i_object', 'user_mentioning', 'object_model, object_id', false);
    }

    public function down()
    {
        echo "m141019_093319_mentioning does not support migration down.\n";
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

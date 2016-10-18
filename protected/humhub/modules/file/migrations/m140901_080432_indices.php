<?php


use yii\db\Migration;

class m140901_080432_indices extends Migration
{

    public function up()
    {
        $this->createIndex('index_object', 'file', 'object_model, object_id', false);
    }

    public function down()
    {
        echo "m140901_080432_indices does not support migration down.\n";
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

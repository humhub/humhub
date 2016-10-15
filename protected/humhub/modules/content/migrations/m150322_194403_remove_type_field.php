<?php


use yii\db\Migration;

class m150322_194403_remove_type_field extends Migration
{

    public function up()
    {
        $this->dropColumn('wall', 'type');
    }

    public function down()
    {
        echo "m150322_194403_remove_type_field does not support migration down.\n";
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

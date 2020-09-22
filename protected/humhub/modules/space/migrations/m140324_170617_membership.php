<?php


use yii\db\Migration;

class m140324_170617_membership extends Migration {

    public function up() {

        $this->renameTable('user_space_membership', 'space_membership');
        
    }

    public function down() {
        echo "m140324_170617_membership does not support migration down.\n";
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

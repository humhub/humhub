<?php


use humhub\components\Migration;

class m140829_122906_delete extends Migration
{
    public function up()
    {
        $this->delete('user', ['status' => 3]);

        $this->safeDropColumn('user', 'user_invite_id');

        # Remove status default value
        $this->safeAlterColumn('user', 'status', 'tinyint(4)');
    }

    public function down()
    {
        echo "m140829_122906_delete does not support migration down.\n";
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

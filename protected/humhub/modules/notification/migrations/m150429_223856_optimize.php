<?php


use yii\db\Migration;

class m150429_223856_optimize extends Migration
{

    public function up()
    {
        $this->createIndex('index_user_id', 'notification', 'user_id', false);
        $this->createIndex('index_seen', 'notification', 'seen', false);
        $this->createIndex('index_desktop_notified', 'notification', 'desktop_notified', false);
        $this->createIndex('index_desktop_emailed', 'notification', 'emailed', false);
    }

    public function down()
    {
        echo "m150429_223856_optimize does not support migration down.\n";
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

<?php

use yii\db\Migration;

class m171025_200312_utf8mb4_fixes extends Migration
{

    public function safeUp()
    {
        $this->alterColumn('user_http_session', 'id', 'char(64) NOT NULL');
        $this->alterColumn('contentcontainer', 'guid', 'char(36) NOT NULL');
        $this->alterColumn('contentcontainer', 'class', 'char(60) NOT NULL');
    }

    public function safeDown()
    {
        echo "m171025_200312_utf8mb4_fixes cannot be reverted.\n";

        return false;
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m171025_200312_utf8mb4_fixes cannot be reverted.\n";

      return false;
      }
     */
}

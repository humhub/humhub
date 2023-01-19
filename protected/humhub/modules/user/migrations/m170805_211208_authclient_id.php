<?php

use yii\db\Migration;

class m170805_211208_authclient_id extends Migration
{

    public function safeUp()
    {
        $this->addColumn('user', 'authclient_id', $this->string(60)->null());
        $this->createIndex('unique_authclient_id', 'user', ['authclient_id'], true);
    }

    public function safeDown()
    {
        echo "m170805_211208_authclient_id cannot be reverted.\n";

        return false;
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m170805_211208_authclient_id cannot be reverted.\n";

      return false;
      }
     */
}

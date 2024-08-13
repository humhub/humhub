<?php

use yii\db\Migration;

/**
 * Adds the capability to disable some BaseNotifications for the web target.
 */
class m170111_190400_disable_web_notifications extends Migration
{

    public function up()
    {
        $this->addColumn('notification', 'send_web_notifications', 'boolean default "1"');
    }

    public function down()
    {
        echo "m170111_190400_disable_web_notifications cannot be reverted.\n";

        return false;
    }

    /*
      // Use safeUp/safeDown to run migration code within a transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}

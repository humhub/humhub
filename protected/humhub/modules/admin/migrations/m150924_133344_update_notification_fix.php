<?php


use humhub\components\Migration;

class m150924_133344_update_notification_fix extends Migration
{

    public function up()
    {
        $this->update('notification', ['class' => 'humhub\modules\admin\notifications\NewVersionAvailable'], ['class' => 'HumHubUpdateNotification']);
    }

    public function down()
    {
        echo "m150924_133344_update_notification_fix cannot be reverted.\n";

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

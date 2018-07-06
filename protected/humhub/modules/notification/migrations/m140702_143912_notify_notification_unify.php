<?php


use yii\db\Migration;

class m140702_143912_notify_notification_unify extends Migration
{

    public function up()
    {
        $this->delete('notification', 'class=:class', [':class' => 'PostCreatedNotification']);
        $this->delete('notification', 'class=:class', [':class' => 'NoteCreatedNotification']);
        $this->delete('notification', 'class=:class', [':class' => 'PollCreatedNotification']);
        $this->delete('notification', 'class=:class', [':class' => 'TaskCreatedNotification']);
    }

    public function down()
    {
        echo "m140702_143912_notify_notification_unify does not support migration down.\n";
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

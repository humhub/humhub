<?php

class m140702_143912_notify_notification_unify extends EDbMigration
{

    public function up()
    {
        $this->delete('notification', 'class=:class', array(':class' => 'PostCreatedNotification'));
        $this->delete('notification', 'class=:class', array(':class' => 'NoteCreatedNotification'));
        $this->delete('notification', 'class=:class', array(':class' => 'PollCreatedNotification'));
        $this->delete('notification', 'class=:class', array(':class' => 'TaskCreatedNotification'));
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

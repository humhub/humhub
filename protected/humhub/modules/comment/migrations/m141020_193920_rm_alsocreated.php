<?php

use yii\db\Schema;
use yii\db\Migration;

class m141020_193920_rm_alsocreated extends Migration
{

    public function up()
    {
        $this->delete('notification', 'class=:alsoComment', array(':alsoComment' => 'AlsoCommentedNotification'));
    }

    public function down()
    {
        echo "m141020_193920_rm_alsocreated does not support migration down.\n";
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

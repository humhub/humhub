<?php


use yii\db\Migration;

class m141020_193931_rm_alsoliked extends Migration
{

    public function up()
    {
        $this->delete('notification', 'class=:alsoLike', array(':alsoLike' => 'AlsoLikesNotification'));
    }

    public function down()
    {
        echo "m141020_193931_rm_alsoliked does not support migration down.\n";
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

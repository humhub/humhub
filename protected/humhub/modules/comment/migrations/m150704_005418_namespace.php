<?php


use humhub\components\Migration;
use humhub\modules\comment\models\Comment;

class m150704_005418_namespace extends Migration
{

    public function up()
    {
        $this->renameClass('Comment', Comment::className());
    }

    public function down()
    {
        echo "m150704_005418_namespace cannot be reverted.\n";

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

<?php


use humhub\components\Migration;
use humhub\modules\notification\models\Notification;
use yii\db\Expression;


class m150703_033650_namespace extends Migration
{

    public function up()
    {
        $this->renameClass('Notification', Notification::className());
        
        $this->update('notification', ['class' => 'humhub\modules\comment\notifications\NewComment'], ['class' => 'NewCommentNotification']);
        $this->update('notification', ['class' => 'humhub\modules\like\notifications\NewLike'], ['class' => 'NewLikeNotification']);
        $this->update('notification', ['class' => 'humhub\modules\user\notifications\Followed'], ['class' => 'FollowNotification']);
        $this->update('notification', ['class' => 'humhub\modules\user\notifications\Mentioned'], ['class' => 'MentionedNotification']);
        $this->update('notification', ['class' => 'humhub\modules\content\notifications\ContentCreated'], ['class' => 'ContentCreatedNotification']);
        
        // Fixes
        $this->update('notification', ['source_class' => new Expression("NULL"), 'source_pk' => new Expression("NULL")], ['class'=>"humhub\modules\user\notifications\Followed"]);
        
        Yii::$app->db->createCommand("UPDATE notification SET originator_user_id = created_by")->execute();
    }

    public function down()
    {
        echo "m150703_033650_namespace cannot be reverted.\n";

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

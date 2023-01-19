<?php


use yii\db\Migration;

class m150831_061628_notifications extends Migration
{

    public function up()
    {
        $this->update('notification', ['class' => 'humhub\modules\space\notifications\ApprovalRequest'], ['class' => 'SpaceApprovalRequestNotification']);
        $this->update('notification', ['class' => 'humhub\modules\space\notifications\ApprovalRequestAccepted'], ['class' => 'SpaceApprovalRequestAcceptedNotification']);
        $this->update('notification', ['class' => 'humhub\modules\space\notifications\ApprovalRequestDeclined'], ['class' => 'SpaceApprovalRequestDeclinedNotification']);
        $this->update('notification', ['class' => 'humhub\modules\space\notifications\Invite'], ['class' => 'SpaceInviteNotification']);
        $this->update('notification', ['class' => 'humhub\modules\space\notifications\InviteAccepted'], ['class' => 'SpaceInviteAcceptedNotification']);
        $this->update('notification', ['class' => 'humhub\modules\space\notifications\InviteDeclined'], ['class' => 'SpaceInviteDeclinedNotification']);


        $this->update('notification', ['source_pk' => new yii\db\Expression('space_id'), 'source_class' => 'humhub\modules\space\models\Space'], ['class' => 'humhub\modules\space\notifications\ApprovalRequest', 'source_class' => 'humhub\modules\user\models\User']);
        $this->update('notification', ['source_pk' => new yii\db\Expression('space_id'), 'source_class' => 'humhub\modules\space\models\Space'], ['class' => 'humhub\modules\space\notifications\ApprovalRequestAccepted', 'source_class' => 'humhub\modules\user\models\User']);
        $this->update('notification', ['source_pk' => new yii\db\Expression('space_id'), 'source_class' => 'humhub\modules\space\models\Space'], ['class' => 'humhub\modules\space\notifications\ApprovalRequestDeclined', 'source_class' => 'humhub\modules\user\models\User']);
        $this->update('notification', ['source_pk' => new yii\db\Expression('space_id'), 'source_class' => 'humhub\modules\space\models\Space'], ['class' => 'humhub\modules\space\notifications\Invite', 'source_class' => 'humhub\modules\user\models\User']);
        $this->update('notification', ['source_pk' => new yii\db\Expression('space_id'), 'source_class' => 'humhub\modules\space\models\Space'], ['class' => 'humhub\modules\space\notifications\InviteAccepted', 'source_class' => 'humhub\modules\user\models\User']);
        $this->update('notification', ['source_pk' => new yii\db\Expression('space_id'), 'source_class' => 'humhub\modules\space\models\Space'], ['class' => 'humhub\modules\space\notifications\InviteDeclined', 'source_class' => 'humhub\modules\user\models\User']);
    }

    public function down()
    {
        echo "m150831_061628_notifications cannot be reverted.\n";

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

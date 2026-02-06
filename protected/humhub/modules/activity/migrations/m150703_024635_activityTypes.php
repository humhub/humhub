<?php


use yii\db\Migration;

class m150703_024635_activityTypes extends Migration
{
    public function up()
    {
        /**
         * Update Core Activity Types
         */
        $this->update('activity', ['class' => 'humhub\modules\space\activities\SpaceCreatedActivity', 'module' => 'space'], ['class' => 'ActivitySpaceCreated']);
        $this->update('activity', ['class' => 'humhub\modules\space\activities\MemberAddedActivity', 'module' => 'space'], ['class' => 'ActivitySpaceMemberAdded']);
        $this->update('activity', ['class' => 'humhub\modules\space\activities\MemberRemovedActivity', 'module' => 'space'], ['class' => 'ActivitySpaceMemberRemoved']);

        $this->update('activity', ['class' => 'humhub\modules\like\activities\LikeActivity', 'module' => 'like'], ['class' => 'Like']);

        $this->update('activity', ['class' => 'humhub\modules\content\activities\ContentCreatedActivity'], ['class' => 'PostCreated']);
        $this->update('activity', ['class' => 'humhub\modules\comment\activities\NewCommentActivity'], ['class' => 'CommentCreated']);
        $this->update('activity', ['class' => 'humhub\modules\user\activities\FollowActivity'], ['class' => 'ActivityUserFollowsUser']);
    }

    public function down()
    {
        echo "m150703_024635_activityTypes cannot be reverted.\n";

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

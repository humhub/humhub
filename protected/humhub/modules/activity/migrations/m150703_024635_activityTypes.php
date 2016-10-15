<?php


use yii\db\Migration;

class m150703_024635_activityTypes extends Migration
{

    public function up()
    {
        /**
         * Update Core Activity Types
         */
        $this->update('activity', ['class' => 'humhub\modules\space\activities\Created', 'module' => 'space'], ['class' => 'ActivitySpaceCreated']);
        $this->update('activity', ['class' => 'humhub\modules\space\activities\MemberAdded', 'module' => 'space'], ['class' => 'ActivitySpaceMemberAdded']);
        $this->update('activity', ['class' => 'humhub\modules\space\activities\MemberRemoved', 'module' => 'space'], ['class' => 'ActivitySpaceMemberRemoved']);
        
        $this->update('activity', ['class' => 'humhub\modules\like\activities\Liked', 'module' => 'like'], ['class' => 'Like']);
        
        $this->update('activity', ['class' => 'humhub\modules\content\activities\ContentCreated'], ['class' => 'PostCreated']);
        $this->update('activity', ['class' => 'humhub\modules\comment\activities\NewComment'], ['class' => 'CommentCreated']);
        $this->update('activity', ['class' => 'humhub\modules\user\activities\UserFollow'], ['class' => 'ActivityUserFollowsUser']);
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

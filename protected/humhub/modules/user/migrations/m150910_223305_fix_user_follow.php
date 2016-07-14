<?php

use humhub\components\Migration;
use humhub\modules\activity\models\Activity;

class m150910_223305_fix_user_follow extends Migration
{

    public function up()
    {
        $activities = (new \yii\db\Query())->select("activity.*, content.user_id, user_follow.id as follow_id")->from('activity')
                ->leftJoin('content', 'content.object_model=:activityModel AND content.object_id=activity.id', [':activityModel' => Activity::className()])
                ->leftJoin('user_follow', 'activity.object_model=user_follow.object_model AND activity.object_id=user_follow.object_id AND user_follow.user_id=content.user_id')
                ->where(['class' => 'humhub\modules\user\activities\UserFollow', 'activity.object_model' => 'humhub\modules\user\models\User']);
        foreach ($activities->each() as $activity) {
            if ($activity['follow_id'] != "") {
                $this->updateSilent('activity', [
                    'object_model' => humhub\modules\user\models\Follow::className(),
                    'object_id' => $activity['follow_id']
                        ], ['id' => $activity['id']]);
            }
        }
    }

    public function down()
    {
        echo "m150910_223305_fix_user_follow cannot be reverted.\n";

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

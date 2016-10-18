<?php


use humhub\components\Migration;
use humhub\modules\activity\models\Activity;
use humhub\modules\space\models\Space;


class m150703_130157_migrate extends Migration
{

    public function up()
    {
        $this->renameClass('Activity', Activity::className());

        // Space Created Activity - object_model/object_id (source fix)
        $activities = (new \yii\db\Query())->select("activity.*, content.space_id")->from('activity')
                        ->leftJoin('content', 'content.object_model=:activityModel AND content.object_id=activity.id', [':activityModel' => Activity::className()])
                        ->where(['class' => 'humhub\modules\space\activities\Created', 'activity.object_model' => ''])->all();
        foreach ($activities as $activity) {
            $this->updateSilent('activity', [
                'object_model' => Space::className(),
                'object_id' => $activity['space_id']
                    ], ['id' => $activity['id']]);
        }

        // Space Member added Activity - object_model/object_id (source fix)
        $activities = (new \yii\db\Query())->select("activity.*, content.space_id")->from('activity')
                        ->leftJoin('content', 'content.object_model=:activityModel AND content.object_id=activity.id', [':activityModel' => Activity::className()])
                        ->where(['class' => 'humhub\modules\space\activities\MemberAdded', 'activity.object_model' => ''])->all();
        foreach ($activities as $activity) {
            $this->updateSilent('activity', [
                'object_model' => Space::className(),
                'object_id' => $activity['space_id']
                    ], ['id' => $activity['id']]);
        }

        // Space Member removed Activity - object_model/object_id (source fix)
        $activities = (new \yii\db\Query())->select("activity.*, content.space_id")->from('activity')
                        ->leftJoin('content', 'content.object_model=:activityModel AND content.object_id=activity.id', [':activityModel' => Activity::className()])
                        ->where(['class' => 'humhub\modules\space\activities\MemberRemoved', 'activity.object_model' => ''])->all();
        foreach ($activities as $activity) {
            $this->updateSilent('activity', [
                'object_model' => Space::className(),
                'object_id' => $activity['space_id']
                    ], ['id' => $activity['id']]);
        }
    }

    public function down()
    {
        echo "m150703_130157_migrate cannot be reverted.\n";

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

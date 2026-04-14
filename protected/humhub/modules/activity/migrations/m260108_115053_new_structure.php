<?php

use humhub\components\Migration;
use humhub\models\RecordMap;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\space\models\Space;
use yii\db\Query;

class m260108_115053_new_structure extends Migration
{
    public function up()
    {

        $renameClasses = [
            'humhub\modules\comment\activities\NewComment' => \humhub\modules\comment\activities\NewCommentActivity::class,
            'humhub\modules\like\activities\Liked' => \humhub\modules\like\activities\LikeActivity::class,
            'humhub\modules\content\activities\ContentCreated' => \humhub\modules\content\activities\ContentCreatedActivity::class,
            'humhub\modules\space\activities\Created' => \humhub\modules\space\activities\SpaceCreatedActivity::class,
            'humhub\modules\space\activities\MemberAdded' => \humhub\modules\space\activities\MemberAddedActivity::class,
            'humhub\modules\space\activities\MemberRemoved' => \humhub\modules\space\activities\MemberRemovedActivity::class,
            'humhub\modules\space\activities\SpaceArchived' => humhub\modules\space\activities\SpaceArchivedActivity::class,
            'humhub\modules\space\activities\SpaceUnArchived' => \humhub\modules\space\activities\SpaceUnArchivedActivity::class,
            'humhub\modules\user\activities\UserFollow' => \humhub\modules\user\activities\FollowActivity::class,
        ];
        foreach ($renameClasses as $oldClass => $newClass) {
            $this->updateSilent('activity', ['class' => $newClass], ['class' => $oldClass]);
        }


        $this->safeAddColumn('activity', 'contentcontainer_id', $this->integer()->null()->after('module'));
        $this->safeAddColumn('activity', 'content_id', $this->integer()->null()->after('contentcontainer_id'));
        $this->safeAddColumn('activity', 'content_addon_record_id', $this->integer()->null()->after('content_id'));
        $this->safeAddColumn('activity', 'created_by', $this->integer());
        $this->safeAddColumn('activity', 'created_at', $this->dateTime());
        $this->alterColumn('activity', 'object_id', $this->integer()->null());


        $this->execute(
            'UPDATE activity
         LEFT JOIN `user_follow` ON activity.object_id = user_follow.id AND activity.object_model=:followType
         LEFT JOIN `user` ON user_follow.object_id = user.id AND user_follow.object_model=:userType
         SET activity.contentcontainer_id=user.contentcontainer_id, activity.object_model=NULL, activity.object_id=NULL
         WHERE activity.object_model=:followType AND user_follow.object_model=:userType',
            ['followType' => \humhub\modules\user\models\Follow::class, 'userType' => \humhub\modules\user\models\User::class],
        );


        $this->safeDropColumn('activity', 'module');
        $this->safeAddForeignKey('activity_content', 'activity', 'content_id', 'content', 'id', 'RESTRICT', 'CASCADE');
        $this->safeAddForeignKey(
            'activity_contentcontainer',
            'activity',
            'contentcontainer_id',
            'contentcontainer',
            'id',
            'RESTRICT',
            'CASCADE',
        );
        $this->safeAddForeignKey('activity_user', 'activity', 'created_by', 'user', 'id', 'RESTRICT', 'CASCADE');

        /**
         * Set Created At/By from Content
         */
        $this->execute(
            'UPDATE activity
         LEFT JOIN `content` ON activity.id=content.object_id AND content.object_model=:activityType
         SET activity.created_by=content.created_by, activity.created_at=content.created_at
         WHERE content.id IS NOT NULL',
            ['activityType' => \humhub\modules\activity\models\Activity::class],
        );

        /**
         * Empty object_model/object_id column when related to Contentcontainer
         */
        $this->execute(
            'UPDATE activity
         LEFT JOIN `space` ON activity.object_id = space.id AND activity.object_model=:spaceType
         SET activity.contentcontainer_id=space.contentcontainer_id, activity.object_model=NULL, activity.object_id=NULL
         WHERE activity.object_model=:spaceType',
            ['spaceType' => Space::class],
        );
        $this->execute(
            'UPDATE activity
         LEFT JOIN `user` ON activity.object_id = user.id AND activity.object_model=:userType
         SET activity.contentcontainer_id=user.contentcontainer_id, activity.object_model=NULL, activity.object_id=NULL
         WHERE activity.object_model=:userType',
            ['userType' => \humhub\modules\user\models\User::class],
        );

        /**
         * If object_model/object_id is related to Content, set content_id instead
         */
        $this->execute(
            'UPDATE activity
         LEFT JOIN `content` ON activity.object_id = content.object_id AND content.object_model=activity.object_model
         SET activity.content_id=content.id, activity.contentcontainer_id=content.contentcontainer_id, activity.object_model=NULL, activity.object_id=NULL
         WHERE content.id IS NOT NULL',
        );

        $this->delete('content', ['object_model' => \humhub\modules\activity\models\Activity::class]);


        /**
         * Add Content Content Addon
         */
        $this->execute(
            'INSERT IGNORE INTO record_map (`model`, `pk`) SELECT DISTINCT a.object_model, a.object_id FROM `activity` a WHERE a.object_model IS NOT NULL AND a.object_model != "";',
        );

        $this->execute(
            'UPDATE `activity` al JOIN record_map rm ON rm.`model` = al.object_model AND rm.`pk` = al.object_id SET al.content_addon_record_id = rm.id WHERE al.content_addon_record_id IS NULL AND al.object_model IS NOT NULL AND al.object_model != "";',
        );

        $this->safeAddForeignKey(
            'fk_activity_content_addon',
            'activity',
            'content_addon_record_id',
            'record_map',
            'id',
            'CASCADE',
            'CASCADE',
        );

        foreach (
            (new Query())->select('content_addon_record_id')->distinct()->from(Activity::tableName())->where(
                'content_id IS NULL and content_addon_record_id IS NOT NULL',
            )->all() as $row
        ) {
            $contentProvider = RecordMap::getById($row['content_addon_record_id'], ContentProvider::class, false);
            if ($contentProvider !== null) {
                if ($contentProvider->content !== null) {
                    $this->updateSilent(
                        'activity',
                        [
                            'content_id' => $contentProvider->content->id,
                            'contentcontainer_id' => $contentProvider->content->contentcontainer_id,
                        ],
                        ['content_addon_record_id' => $row['content_addon_record_id']],
                    );
                } else {
                    Yii::warning(
                        'Content Provider ' . get_class(
                            $contentProvider,
                        ) . ' with id ' . $contentProvider->id . ' has no content!',
                        'activity',
                    );
                }
            } else {
                $orphan = (new Query())->select('*')->distinct()->from(Activity::tableName())->where(['content_addon_record_id' => $row['content_addon_record_id']])->one();
                if (!empty($orphan['class']) && $orphan['class'] === \humhub\modules\user\activities\FollowActivity::class) {
                    // We have sometimes wrong records here.
                    $this->delete('activity', ['content_addon_record_id' => $row['content_addon_record_id']]);
                    $this->delete('record_map', ['id' => $row['content_addon_record_id']]);
                } else {
                    Yii::warning(
                        'Delete Activity with content_addon_record_id ' . $row['content_addon_record_id'],
                        'activity',
                    );
                }

            }
        }

        $this->safeDropColumn('activity', 'object_model');
        $this->safeDropColumn('activity', 'object_id');

    }

    public function down()
    {
        echo "m170112_115052_settings cannot be reverted.\n";

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

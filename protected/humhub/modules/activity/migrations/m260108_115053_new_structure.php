<?php

use humhub\components\Migration;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\space\models\Space;
use yii\db\Query;

class m260108_115053_new_structure extends Migration
{
    public function up()
    {
        // ---------------------------------------------------------------
        // Phase 1 — idempotent schema additions and class-name renames.
        // Safe to re-run after any failure in later phases.
        // ---------------------------------------------------------------

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

        $this->safeAddColumn('activity', 'contentcontainer_id', $this->integer()->null()->after('class'));
        $this->safeAddColumn('activity', 'content_id', $this->integer()->null()->after('contentcontainer_id'));
        $this->safeAddColumn('activity', 'content_addon_record_id', $this->integer()->null()->after('content_id'));
        $this->safeAddColumn('activity', 'created_by', $this->integer());
        $this->safeAddColumn('activity', 'created_at', $this->dateTime());

        if ($this->columnExists('object_id', 'activity')) {
            $this->alterColumn('activity', 'object_id', $this->integer()->null());
        }

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
        $this->safeAddForeignKey(
            'fk_activity_content_addon',
            'activity',
            'content_addon_record_id',
            'record_map',
            'id',
            'CASCADE',
            'CASCADE',
        );

        // ---------------------------------------------------------------
        // Phase 2 — data migration. Only runs while the legacy polymorphic
        // columns still exist. Skipped on a re-run that already reached the
        // final drops in Phase 3 (each statement is also idempotent because
        // it nulls out the legacy columns as a sentinel).
        // ---------------------------------------------------------------

        if ($this->columnExists('object_model', 'activity') && $this->columnExists('object_id', 'activity')) {
            $this->execute(
                'UPDATE activity
             LEFT JOIN `user_follow` ON activity.object_id = user_follow.id AND activity.object_model=:followType
             LEFT JOIN `user` ON user_follow.object_id = user.id AND user_follow.object_model=:userType
             SET activity.contentcontainer_id=user.contentcontainer_id, activity.object_model=NULL, activity.object_id=NULL
             WHERE activity.object_model=:followType AND user_follow.object_model=:userType',
                ['followType' => \humhub\modules\user\models\Follow::class, 'userType' => \humhub\modules\user\models\User::class],
            );

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

            // Resolve content_id + contentcontainer_id for activities on content
            // addons set-based (one UPDATE per addon model) instead of resolving
            // each record individually. Addons store their parent via content_id
            // (ContentAddonActiveRecord::getContent()); contentcontainer_id is
            // taken from the joined content row.
            $addonModels = (new Query())
                ->select('object_model')
                ->distinct()
                ->from(Activity::tableName())
                ->where('content_id IS NULL AND content_addon_record_id IS NOT NULL AND object_model IS NOT NULL AND object_model != ""')
                ->column();

            foreach ($addonModels as $model) {
                $isContentProvider = class_exists($model) && is_subclass_of($model, ContentProvider::class);

                if ($isContentProvider && $this->columnExists('content_id', $model::tableName())) {
                    // Standard content addon: parent content_id lives on the addon row,
                    // contentcontainer_id comes from the joined content (which also acts
                    // as the "has content" guard via INNER JOIN).
                    $this->execute(
                        'UPDATE `activity` al
                         JOIN ' . $this->db->quoteTableName($model::tableName()) . ' a ON a.id = al.object_id
                         JOIN `content` c ON c.id = a.content_id
                         SET al.content_id = c.id, al.contentcontainer_id = c.contentcontainer_id
                         WHERE al.content_id IS NULL
                           AND al.content_addon_record_id IS NOT NULL
                           AND al.object_model = :model',
                        ['model' => $model],
                    );
                    continue;
                }

                if ($isContentProvider) {
                    // ContentProvider without a content_id column – resolve per record (rare).
                    foreach (
                        (new Query())->select(['id', 'object_id'])->from(Activity::tableName())
                            ->where(['content_id' => null, 'object_model' => $model])
                            ->andWhere('content_addon_record_id IS NOT NULL AND object_id IS NOT NULL')->each() as $row
                    ) {
                        $addon = $model::findOne(['id' => $row['object_id']]);
                        if ($addon !== null && $addon->content !== null) {
                            $this->updateSilent(
                                'activity',
                                [
                                    'content_id' => $addon->content->id,
                                    'contentcontainer_id' => $addon->content->contentcontainer_id,
                                ],
                                ['id' => $row['id']],
                            );
                        }
                    }
                    continue;
                }

                // Model is not a ContentProvider (e.g. a ContentActiveRecord like
                // ContainerSnippet, or a class that no longer exists). Its activities
                // were already handled by the content-JOIN UPDATEs in Phase 2 above.
                // Any that still have content_id=NULL here have no resolvable parent
                // (deleted content, removed module) and must be cleaned up so they
                // do not violate future NOT NULL constraints on content_id.
                $orphanRecordMapIds = (new Query())
                    ->select('content_addon_record_id')
                    ->distinct()
                    ->from(Activity::tableName())
                    ->where(['content_id' => null, 'object_model' => $model])
                    ->andWhere('content_addon_record_id IS NOT NULL')
                    ->column();

                if ($orphanRecordMapIds !== []) {
                    Yii::warning(
                        'Deleting ' . count($orphanRecordMapIds) . ' unresolvable activity record(s) for model: ' . $model,
                        'activity',
                    );
                    $this->delete('activity', ['content_addon_record_id' => $orphanRecordMapIds]);
                    $this->delete('record_map', ['id' => $orphanRecordMapIds]);
                }
            }
        }

        // ---------------------------------------------------------------
        // Phase 3 — final cleanup. All drops happen here, after all data
        // migration is finished, so a partial failure in Phase 2 still
        // leaves the legacy columns intact for a resumable re-run.
        // ---------------------------------------------------------------

        $this->safeDropColumn('activity', 'module');
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

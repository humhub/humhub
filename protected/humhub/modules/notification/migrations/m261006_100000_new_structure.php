<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Restructures the `notification` table: foreign keys instead of the polymorphic
 * `source_class`/`source_pk`, a write-time grouping key, `seen_at`, `listed` and `priority`.
 *
 * Re-runnable after a failure: phase 1 and 3 only use idempotent helpers, phase 2 runs
 * while the legacy columns still exist.
 *
 * @since 1.20
 */
class m261006_100000_new_structure extends Migration
{
    /**
     * Core notification classes renamed in 1.20 (old => new)
     */
    public const RENAMED_CLASSES = [
        'humhub\modules\admin\notifications\NewVersionAvailable' => 'humhub\modules\admin\notifications\NewVersionAvailableNotification',
        'humhub\modules\comment\notifications\NewComment' => 'humhub\modules\comment\notifications\NewCommentNotification',
        'humhub\modules\comment\notifications\CommentDeleted' => 'humhub\modules\comment\notifications\CommentDeletedNotification',
        'humhub\modules\content\notifications\ContentCreated' => 'humhub\modules\content\notifications\ContentCreatedNotification',
        'humhub\modules\content\notifications\ContentDeleted' => 'humhub\modules\content\notifications\ContentDeletedNotification',
        'humhub\modules\friendship\notifications\Request' => 'humhub\modules\friendship\notifications\FriendshipRequestNotification',
        'humhub\modules\friendship\notifications\RequestApproved' => 'humhub\modules\friendship\notifications\FriendshipApprovedNotification',
        'humhub\modules\friendship\notifications\RequestDeclined' => 'humhub\modules\friendship\notifications\FriendshipDeclinedNotification',
        'humhub\modules\like\notifications\NewLike' => 'humhub\modules\like\notifications\NewLikeNotification',
        'humhub\modules\space\notifications\Invite' => 'humhub\modules\space\notifications\SpaceInviteNotification',
        'humhub\modules\space\notifications\InviteAccepted' => 'humhub\modules\space\notifications\SpaceInviteAcceptedNotification',
        'humhub\modules\space\notifications\InviteDeclined' => 'humhub\modules\space\notifications\SpaceInviteDeclinedNotification',
        'humhub\modules\space\notifications\InviteRevoked' => 'humhub\modules\space\notifications\SpaceInviteRevokedNotification',
        'humhub\modules\space\notifications\ApprovalRequest' => 'humhub\modules\space\notifications\SpaceApprovalRequestNotification',
        'humhub\modules\space\notifications\ApprovalRequestAccepted' => 'humhub\modules\space\notifications\SpaceApprovalAcceptedNotification',
        'humhub\modules\space\notifications\ApprovalRequestDeclined' => 'humhub\modules\space\notifications\SpaceApprovalDeclinedNotification',
        'humhub\modules\space\notifications\ChangedRolesMembership' => 'humhub\modules\space\notifications\SpaceRolesChangedNotification',
        'humhub\modules\space\notifications\SpaceCreated' => 'humhub\modules\space\notifications\SpaceCreatedNotification',
        'humhub\modules\user\notifications\Followed' => 'humhub\modules\user\notifications\FollowedNotification',
        'humhub\modules\user\notifications\Mentioned' => 'humhub\modules\user\notifications\MentionedNotification',
    ];

    private const COMMENT_CLASS = 'humhub\modules\comment\models\Comment';
    private const LIKE_CLASS = 'humhub\modules\like\models\Like';
    private const SPACE_CLASS = 'humhub\modules\space\models\Space';
    private const USER_CLASS = 'humhub\modules\user\models\User';

    public function up()
    {
        // ---------------------------------------------------------------
        // Phase 1 — idempotent schema additions.
        // ---------------------------------------------------------------

        $this->alterColumn('notification', 'class', $this->string(255)->notNull());

        $this->safeAddColumn('notification', 'originator_id', $this->integer()->null()->after('user_id'));
        $this->safeAddColumn('notification', 'content_id', $this->integer()->null()->after('originator_id'));
        $this->safeAddColumn('notification', 'contentcontainer_id', $this->integer()->null()->after('content_id'));
        $this->safeAddColumn('notification', 'source_record_id', $this->integer()->null()->after('contentcontainer_id'));
        $this->safeAddColumn('notification', 'grouping_key', $this->integer()->null()->after('source_record_id'));
        $this->safeAddColumn('notification', 'priority', $this->tinyInteger()->notNull()->defaultValue(1)->after('grouping_key'));
        $this->safeAddColumn('notification', 'listed', $this->tinyInteger()->notNull()->defaultValue(1)->after('priority'));
        $this->safeAddColumn('notification', 'seen_at', $this->dateTime()->null()->after('payload'));

        // ---------------------------------------------------------------
        // Phase 2 — data migration, only while the legacy columns exist.
        // Every statement is idempotent, so a re-run after a failure is safe.
        // ---------------------------------------------------------------

        if ($this->columnExists('source_class', 'notification')) {
            $this->migrateData();
        }

        // ---------------------------------------------------------------
        // Phase 3 — foreign keys, indexes and the drop of the legacy columns.
        // ---------------------------------------------------------------

        // Replaced by fk_notification_user
        $this->safeDropForeignKey('fk_notification-user_id', 'notification');

        $this->safeAddForeignKey('fk_notification_user', 'notification', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->safeAddForeignKey('fk_notification_originator', 'notification', 'originator_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->safeAddForeignKey('fk_notification_content', 'notification', 'content_id', 'content', 'id', 'CASCADE', 'CASCADE');
        $this->safeAddForeignKey('fk_notification_contentcontainer', 'notification', 'contentcontainer_id', 'contentcontainer', 'id', 'CASCADE', 'CASCADE');
        $this->safeAddForeignKey('fk_notification_source_record', 'notification', 'source_record_id', 'record_map', 'id', 'CASCADE', 'CASCADE');

        $this->safeCreateIndex('idx_notification_list', 'notification', ['user_id', 'listed', 'seen_at']);
        $this->safeCreateIndex('idx_notification_group', 'notification', ['user_id', 'grouping_key']);
        $this->safeCreateIndex('idx_notification_created', 'notification', ['created_at']);

        // Legacy indexes: on dropped columns, or covered by idx_notification_list
        foreach (['index_groupuser', 'index_seen', 'index_desktop_emailed', 'index_user_id'] as $index) {
            if ($this->indexExists($index, 'notification')) {
                $this->dropIndex($index, 'notification');
            }
        }

        foreach (
            [
                'source_class', 'source_pk', 'space_id', 'module', 'emailed',
                'send_web_notifications', 'seen', 'group_key', 'originator_user_id',
            ] as $column
        ) {
            $this->safeDropColumn('notification', $column);
        }
    }

    private function migrateData(): void
    {
        $like = $this->db->quoteTableName('like');

        // Originator: only existing users, so a re-run after fk_notification_originator exists cannot fail
        $this->execute('UPDATE notification n JOIN `user` u ON u.id = n.originator_user_id SET n.originator_id = u.id');
        $this->execute('UPDATE notification n LEFT JOIN `user` u ON u.id = n.originator_id
            SET n.originator_id = NULL WHERE n.originator_id IS NOT NULL AND u.id IS NULL');

        // Sources that are a content record
        $this->execute('UPDATE notification n
            JOIN content c ON c.object_model = n.source_class AND c.object_id = n.source_pk
            SET n.content_id = c.id, n.contentcontainer_id = c.contentcontainer_id');

        $this->deleteDanglingSources();

        // Comment sources: the comment's content, the comment itself as source record
        $this->execute(
            'INSERT IGNORE INTO record_map (`model`, `pk`)
            SELECT DISTINCT n.source_class, n.source_pk FROM notification n
            WHERE n.source_class = :comment AND n.source_pk IS NOT NULL',
            ['comment' => self::COMMENT_CLASS],
        );
        $this->execute(
            'UPDATE notification n
            JOIN comment cm ON n.source_class = :comment AND cm.id = n.source_pk
            JOIN content c ON c.id = cm.content_id
            JOIN record_map rm ON rm.`model` = n.source_class AND rm.`pk` = n.source_pk
            SET n.content_id = c.id, n.contentcontainer_id = c.contentcontainer_id, n.source_record_id = rm.id',
            ['comment' => self::COMMENT_CLASS],
        );

        // Like sources (NewLike stored the Like record as source): the liked content, and the
        // liked content addon (e.g. a comment) as source record. Since 1.20 the `like` table
        // holds both itself (m260105_094333_like_contentid, m260106_175102_like_record_map).
        $this->execute(
            "UPDATE notification n
            JOIN $like l ON n.source_class = :like AND l.id = n.source_pk
            JOIN content c ON c.id = l.content_id
            SET n.content_id = c.id, n.contentcontainer_id = c.contentcontainer_id,
                n.source_record_id = l.content_addon_record_id",
            ['like' => self::LIKE_CLASS],
        );

        // Container sources
        $this->execute(
            'UPDATE notification n JOIN space s ON n.source_class = :space AND s.id = n.source_pk
            SET n.contentcontainer_id = s.contentcontainer_id',
            ['space' => self::SPACE_CLASS],
        );
        $this->execute(
            'UPDATE notification n JOIN `user` u ON n.source_class = :user AND u.id = n.source_pk
            SET n.contentcontainer_id = u.contentcontainer_id',
            ['user' => self::USER_CLASS],
        );

        // Every other source record (groups, memberships, follows, friendships, module records)
        $this->execute("INSERT IGNORE INTO record_map (`model`, `pk`)
            SELECT DISTINCT n.source_class, n.source_pk FROM notification n
            WHERE n.content_id IS NULL AND n.contentcontainer_id IS NULL
              AND n.source_class IS NOT NULL AND n.source_class != '' AND n.source_pk IS NOT NULL");
        $this->execute('UPDATE notification n
            JOIN record_map rm ON rm.`model` = n.source_class AND rm.`pk` = n.source_pk
            SET n.source_record_id = rm.id
            WHERE n.content_id IS NULL AND n.contentcontainer_id IS NULL');

        // A source that resolved to nothing: the row is unusable
        $this->execute("DELETE FROM notification
            WHERE source_class IS NOT NULL AND source_class != ''
              AND content_id IS NULL AND contentcontainer_id IS NULL AND source_record_id IS NULL");

        // Legacy space_id for rows still without a container
        $this->execute('UPDATE notification n JOIN space s ON s.id = n.space_id
            SET n.contentcontainer_id = s.contentcontainer_id
            WHERE n.contentcontainer_id IS NULL AND n.space_id IS NOT NULL');

        // Seen, listed, grouping
        $this->execute('UPDATE notification SET seen_at = created_at WHERE seen = 1 AND seen_at IS NULL');
        $this->execute('UPDATE notification SET listed = COALESCE(send_web_notifications, 0)');
        $this->execute('UPDATE notification SET grouping_key = id');
        // Derived table: MySQL refuses a subquery on the updated table (error 1093)
        $this->execute("UPDATE notification n
            JOIN (
                SELECT user_id, class, group_key, MAX(id) AS head FROM notification
                WHERE group_key IS NOT NULL AND group_key != ''
                GROUP BY user_id, class, group_key
            ) g ON g.user_id = n.user_id AND g.class = n.class AND g.group_key = n.group_key
            SET n.grouping_key = g.head");

        // Renamed core classes
        foreach (self::RENAMED_CLASSES as $old => $new) {
            $this->updateSilent('notification', ['class' => $new], ['class' => $old]);
        }

        // Recipients that no longer exist
        $this->execute('DELETE n FROM notification n LEFT JOIN `user` u ON u.id = n.user_id WHERE u.id IS NULL');
    }

    /**
     * Deletes the rows not resolved to a content yet whose source record no longer exists,
     * so neither a `record_map` row is created for them nor they survive with a dangling
     * `source_record_id`.
     */
    private function deleteDanglingSources(): void
    {
        $unresolved = "n.content_id IS NULL AND n.contentcontainer_id IS NULL
            AND n.source_class IS NOT NULL AND n.source_class != '' AND n.source_pk IS NOT NULL";

        $models = (new Query())
            ->select('n.source_class')
            ->distinct()
            ->from(['n' => 'notification'])
            ->where($unresolved)
            ->column($this->db);

        foreach ($models as $model) {
            // Read the schema directly: ActiveRecord::primaryKey() throws when the table is gone
            $schema = class_exists($model) && is_subclass_of($model, ActiveRecord::class)
                ? $this->db->getTableSchema($model::tableName(), true)
                : null;

            if ($schema !== null && $schema->primaryKey === ['id']) {
                $this->execute(
                    'DELETE n FROM notification n
                    LEFT JOIN ' . $this->db->quoteTableName($model::tableName()) . " t ON t.id = n.source_pk
                    WHERE n.source_class = :model AND $unresolved AND t.id IS NULL",
                    ['model' => $model],
                );
                continue;
            }

            // Class of an uninstalled module, no longer an ActiveRecord, without an `id` primary key
            // or without its table: the source cannot be resolved
            $count = $this->db->createCommand(
                "DELETE n FROM notification n WHERE n.source_class = :model AND $unresolved",
                ['model' => $model],
            )->execute();
            Yii::warning('Deleted ' . $count . ' unresolvable notification(s) of source model: ' . $model, 'notification');
        }
    }

    public function down()
    {
        echo "m261006_100000_new_structure cannot be reverted.\n";

        return false;
    }
}

<?php

namespace humhub\modules\like\services;

use humhub\models\RecordMap;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\Content;
use humhub\modules\like\activities\LikeActivity as LikedActivity;
use humhub\modules\like\models\Like;
use humhub\modules\like\notifications\NewLike as NewLikeNotification;
use humhub\modules\like\permissions\CanLike;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\IntegrityException;

class LikeService
{
    /**
     * @var int seconds to wait for the lock serializing concurrent likes
     */
    private const LOCK_TIMEOUT = 10;

    private readonly Content $content;
    private ?ContentAddonActiveRecord $contentAddon = null;
    private readonly ?User $user;
    private ?int $_count = null;
    private ?bool $_hasLiked = null;


    public function __construct(ContentProvider $object, ?User $user = null)
    {
        $this->content = $object->content;

        if ($object instanceof ContentAddonActiveRecord) {
            $this->contentAddon = $object;
        }

        $this->user = $user ?? Yii::$app->getUser()->identity ?? null;
    }

    /**
     * Like counts of many records at once, in ONE query - `record id => count`, records
     * without a single like absent.
     *
     * Takes the loaded records rather than bare ids because the like table addresses the two
     * kinds of likeable record differently (see {@see self::addScopeQueryCondition()}): a
     * content addon by its platform record id, a content record by its content id with no
     * addon id at all. Feed the result into {@see self::preloadState()}.
     *
     * @param array<int, ContentProvider> $records record id => record
     * @return array<int, int>
     * @since 1.20
     */
    public static function countsForRecords(array $records): array
    {
        [$condition, $recordIdByKey] = static::buildBatchCondition($records);

        if ($condition === null) {
            return [];
        }

        $rows = Like::find()
            ->select(['content_id', 'content_addon_record_id', 'total' => new Expression('COUNT(*)')])
            ->where($condition)
            ->groupBy(['content_id', 'content_addon_record_id'])
            ->asArray()
            ->all();

        $counts = [];
        foreach ($rows as $row) {
            $recordId = static::resolveBatchRow($row, $recordIdByKey);
            if ($recordId !== null) {
                $counts[$recordId] = (int)$row['total'];
            }
        }

        return $counts;
    }

    /**
     * Which of the given records the user has liked, in ONE query.
     *
     * @param array<int, ContentProvider> $records record id => record
     * @param User|null $user defaults to the current identity; a guest has liked nothing
     * @return int[] the record ids the user has liked
     * @since 1.20
     */
    public static function likedRecordIds(array $records, ?User $user = null): array
    {
        $user ??= Yii::$app->getUser()->identity ?? null;
        [$condition, $recordIdByKey] = static::buildBatchCondition($records);

        if ($condition === null || $user === null) {
            return [];
        }

        $rows = Like::find()
            ->select(['content_id', 'content_addon_record_id'])
            ->where($condition)
            ->andWhere(['created_by' => $user->id])
            ->asArray()
            ->all();

        $recordIds = [];
        foreach ($rows as $row) {
            $recordId = static::resolveBatchRow($row, $recordIdByKey);
            if ($recordId !== null) {
                $recordIds[] = $recordId;
            }
        }

        return $recordIds;
    }

    /**
     * The `like` condition covering a mixed set of records, plus the lookup mapping a result
     * row back to its record id.
     *
     * @param array<int, ContentProvider> $records record id => record
     * @return array{0: array|null, 1: array<string, int>} condition (null for no records) and
     *         `"<contentId>:<addonRecordId|>" => record id`
     */
    private static function buildBatchCondition(array $records): array
    {
        $addonRecordIds = [];
        $contentIds = [];
        $recordIdByKey = [];

        foreach ($records as $recordId => $record) {
            $contentId = (int)$record->content->id;

            if ($record instanceof ContentAddonActiveRecord) {
                $addonRecordIds[] = (int)$recordId;
                $recordIdByKey[$contentId . ':' . (int)$recordId] = (int)$recordId;
            } else {
                $contentIds[] = $contentId;
                $recordIdByKey[$contentId . ':'] = (int)$recordId;
            }
        }

        $condition = ['or'];
        if ($addonRecordIds !== []) {
            $condition[] = ['content_addon_record_id' => $addonRecordIds];
        }
        if ($contentIds !== []) {
            $condition[] = ['and', ['content_id' => $contentIds], ['content_addon_record_id' => null]];
        }

        return [count($condition) > 1 ? $condition : null, $recordIdByKey];
    }

    /**
     * @param array $row a `like` row with `content_id` and `content_addon_record_id`
     * @param array<string, int> $recordIdByKey see {@see self::buildBatchCondition()}
     */
    private static function resolveBatchRow(array $row, array $recordIdByKey): ?int
    {
        $addonRecordId = $row['content_addon_record_id'] === null ? '' : (int)$row['content_addon_record_id'];

        return $recordIdByKey[(int)$row['content_id'] . ':' . $addonRecordId] ?? null;
    }

    /**
     * Seeds the like count and the caller's own like state, so {@see self::getCount()} and
     * {@see self::hasLiked()} answer without querying.
     *
     * For callers that already know both from a batched query over many records - see
     * {@see self::countsForRecords()}/{@see self::likedRecordIds()}, which the platform's
     * batched like-state endpoint uses to answer a whole comment window in two queries.
     * Everything else about the state (above all {@see self::canLike()}, which touches no
     * like row at all) then still comes from this one implementation instead of being
     * rebuilt at the call site.
     *
     * @since 1.20
     */
    public function preloadState(int $count, bool $hasLiked): static
    {
        $this->_count = $count;
        $this->_hasLiked = $hasLiked;

        return $this;
    }

    public function canLike(): bool
    {
        if (!(Yii::$app->getModule('like'))->isEnabled || !$this->user) {
            return false;
        }

        if (!$this->content->getStateService()->isPublished() || $this->content->isArchived()) {
            return false;
        }

        if (isset($this->content->container) && !$this->content->container->can(new CanLike())) {
            return false;
        }

        return true;
    }

    public function hasLiked(): bool
    {
        if (!$this->user) {
            return false;
        }

        if ($this->_hasLiked === null) {
            $query = Like::find();
            $this->addScopeQueryCondition($query);
            $query->andWhere(['created_by' => $this->user->id]);

            $this->_hasLiked = ($query->count() !== 0);
        }

        return $this->_hasLiked;
    }

    public function like(): bool
    {
        if (!$this->user) {
            return false;
        }

        // Serialize concurrent requests all liking the same target. The unique index
        // on `like` cannot be relied on here: a like on the content itself stores NULL
        // in content_addon_record_id, and MySQL/MariaDB treat NULLs in a unique index
        // as distinct, so a duplicate row would pass unnoticed and inflate the counter.
        $mutex = Yii::$app->mutex;
        $lockName = $this->getLockName();
        $locked = $mutex->acquire($lockName, self::LOCK_TIMEOUT);

        try {
            $record = $this->createLikeRecord();
        } finally {
            if ($locked) {
                $mutex->release($lockName);
            }
        }

        if ($record === null) {
            return false;
        }

        $this->reset();

        $author = $this->contentAddon->createdBy ?? $this->content->createdBy;
        NewLikeNotification::instance()->from($this->user)->about($record)->send($author);

        ActivityManager::dispatch(LikedActivity::class, $record, $record->createdBy);

        return true;
    }

    /**
     * Creates the like record, unless the user already likes the target. Only to be
     * called while holding the lock of [[like()]], since the check and the insert are
     * not atomic on their own.
     *
     * @return Like|null the new record, or null if the target is already liked
     */
    private function createLikeRecord(): ?Like
    {
        // Re-check after waiting for the lock - a concurrent request may have
        // created the like in the meantime
        if ($this->getCurrentLikeRecord()) {
            return null;
        }

        $record = new Like();
        $record->content_id = $this->content->id;
        if ($this->contentAddon) {
            $record->content_addon_record_id = RecordMap::getId($this->contentAddon);
        } else {
            $record->content_addon_record_id = new Expression('NULL');
        }

        try {
            return $record->save() ? $record : null;
        } catch (IntegrityException $e) {
            // Only reachable when the lock could not be acquired: for a like on a
            // content addon the unique index still catches the duplicate. Verify
            // before ignoring, don't swallow other errors.
            $this->reset();

            if (!$this->hasLiked()) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * @return string the lock name serializing concurrent likes on the same target
     */
    private function getLockName(): string
    {
        return sprintf(
            'like.%d.%d.%d',
            $this->content->id,
            $this->contentAddon ? RecordMap::getId($this->contentAddon) : 0,
            $this->user->id,
        );
    }

    public function unlike(): bool
    {
        if (!$this->user) {
            return false;
        }

        $like = $this->getCurrentLikeRecord();
        if ($like) {
            $like->delete();
            $this->reset();
            return true;
        }

        return false;
    }

    private function getCurrentLikeRecord(): ?Like
    {
        if (!$this->user) {
            return null;
        }

        $query = Like::find();
        $query->andWhere(['created_by' => $this->user->id]);
        $this->addScopeQueryCondition($query);
        return $query->one();
    }

    public function getCount(): int
    {
        if ($this->_count === null) {
            $query = Like::find();
            $this->addScopeQueryCondition($query);
            $this->_count = $query->count();
        }

        return $this->_count;
    }

    public function getUserQuery(): ActiveQueryUser
    {
        $query = User::find();
        $query->leftJoin('like', 'like.created_by=user.id');
        $this->addScopeQueryCondition($query);
        // `like.id DESC` is a tiebreaker for likes sharing the same `created_at` (its
        // datetime column only has one-second resolution) - without it, offset/limit
        // pagination over ties has no stable order and can show the same liker twice
        // or skip one across two pages (see the like module's user-list JSON endpoint,
        // `LikeController::actionUserList()`).
        $query->orderBy('like.created_at DESC, like.id DESC');

        return $query;
    }

    public function addScopeQueryCondition(ActiveQuery $query): void
    {
        $query->andWhere(['like.content_id' => $this->content->id]);

        if ($this->contentAddon) {
            $query->andWhere(['like.content_addon_record_id' => RecordMap::getId($this->contentAddon)]);
        } else {
            $query->andWhere('like.content_addon_record_id IS NULL');
        }
    }

    private function getCacheKey(): string
    {
        return sprintf('like.%d.%d.%d', $this->content->id, $this->contentAddon->id, $this->user->id ?? 0);
    }

    private function reset(): void
    {
        $this->_count = null;
        $this->_hasLiked = null;
    }
}

<?php

namespace humhub\modules\activity\services;

use humhub\models\RecordMap;
use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\notification\components\Grouping;
use humhub\modules\user\models\User;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Groups an activity with its siblings: those matched by the {@see Grouping} of its class
 * ({@see BaseActivity::grouping()}) - or, when the class overrides the deprecated
 * {@see BaseActivity::getGroupingQuery()}, by that query, with the deprecated
 * `$groupingThreshold` and `$groupingTimeBucketSeconds`.
 *
 * The group query of a declared grouping: the activities of the same class, created in the same
 * time bucket, matching the activity in what the grouping names - `content_id` ({@see Grouping::byContent()}),
 * `content_addon_record_id` ({@see Grouping::bySource()}), `contentcontainer_id` ({@see Grouping::byContainer()}),
 * `created_by` ({@see Grouping::andOriginator()}), the `object_model` and `visibility` of the
 * content ({@see Grouping::andContentType()}, {@see Grouping::andContentVisibility()}) and the
 * `content_addon_record_id` of the source addon ({@see Grouping::andSourceParent()}).
 * {@see Grouping::unseenOnly()} is rejected: an activity is not seen by anybody.
 *
 * @internal
 */
final class GroupingService
{
    /**
     * The `activity` columns of the things a {@see Grouping} compares.
     */
    private const COLUMNS = [
        Grouping::CONTENT => 'content_id',
        Grouping::SOURCE => 'content_addon_record_id',
        Grouping::CONTAINER => 'contentcontainer_id',
        Grouping::ORIGINATOR => 'created_by',
    ];

    private ?array $_groupedUsers = null;

    private ?BaseActivity $_sibling = null;

    /**
     * @var int the minimum number of activities that form a group
     */
    private readonly int $threshold;

    public ?ActiveQueryActivity $groupQuery;

    public function __construct(private readonly BaseActivity $activity)
    {
        $grouping = self::overridesGroupingQuery($activity) ? null : $activity::grouping();

        if ($grouping !== null) {
            $this->threshold = $grouping->getThreshold();
            $this->groupQuery = $activity->isGroupable() ? $this->buildGroupQuery($grouping) : null;
            return;
        }

        // A class overriding the deprecated getGroupingQuery() (or without any grouping), as up to 1.19
        $this->threshold = $activity->groupingThreshold;
        $this->groupQuery = $activity->getGroupingQuery();
        $this->groupQuery?->timeBucket($activity->groupingTimeBucketSeconds, $activity->createdAt);
    }

    /**
     * Whether the class of the activity overrides the deprecated {@see BaseActivity::getGroupingQuery()}:
     * that override wins over {@see BaseActivity::grouping()}, so a 1.19 subclass of a core activity
     * keeps its own grouping.
     */
    private static function overridesGroupingQuery(BaseActivity $activity): bool
    {
        static $overrides = [];

        return $overrides[$activity::class] ??= (new \ReflectionMethod($activity, 'getGroupingQuery'))
            ->getDeclaringClass()->getName() !== BaseActivity::class;
    }

    /**
     * The query of the activities this one is grouped with; `null` when the activity lacks
     * something the grouping requires.
     *
     * @throws InvalidConfigException for a grouping with a modifier activities do not support
     */
    private function buildGroupQuery(Grouping $grouping): ?ActiveQueryActivity
    {
        if ($grouping->isUnseenOnly()) {
            throw new InvalidConfigException(
                'The grouping of ' . $this->activity::class . ' uses unseenOnly(), which activities do not support.',
            );
        }

        $record = $this->activity->record;
        $query = Activity::find()
            ->andWhere(['activity.class' => $record->class])
            ->timeBucket($grouping->getTimeBucket(), $record->created_at);

        foreach ($grouping->getRequired() as $name) {
            $value = $record->getAttribute(self::COLUMNS[$name]);
            if ($value === null) {
                return null;
            }
            $query->andWhere(['activity.' . self::COLUMNS[$name] => $value]);
        }

        foreach ($grouping->getMatched() as $name) {
            if ($name === Grouping::CONTENT_TYPE || $name === Grouping::CONTENT_VISIBILITY) {
                // `content` is joined by ActiveQueryActivity
                $column = $name === Grouping::CONTENT_TYPE ? 'object_model' : 'visibility';
                $value = $record->content?->getAttribute($column);
                if ($value === null) {
                    return null;
                }
                $query->andWhere(['content.' . $column => $value]);
                continue;
            }

            if ($name === Grouping::SOURCE_PARENT) {
                if (!$this->matchSourceParent($query)) {
                    return null;
                }
                continue;
            }

            $query->andWhere(['activity.' . self::COLUMNS[$name] => $record->getAttribute(self::COLUMNS[$name])]);
        }

        return $query;
    }

    /**
     * Narrows the query to the activities whose source addon belongs to the same content addon
     * (`content_addon_record_id` of the addon, e.g. of a like) as the source of this activity;
     * `false` when the activity has no such source.
     */
    private function matchSourceParent(ActiveQueryActivity $query): bool
    {
        $sourceId = $this->activity->record->content_addon_record_id;
        $source = $sourceId !== null ? RecordMap::getById((int)$sourceId, ActiveRecord::class) : null;
        if ($source === null || !$source->hasAttribute('content_addon_record_id')) {
            return false;
        }

        $query
            ->innerJoin(['grouping_source_map' => RecordMap::tableName()], 'grouping_source_map.id = activity.content_addon_record_id')
            ->innerJoin(
                ['grouping_source' => $source::tableName()],
                'grouping_source.' . $source::primaryKey()[0] . ' = grouping_source_map.pk AND grouping_source_map.model = :groupingSourceModel',
                [':groupingSourceModel' => $source::class],
            )
            ->andWhere(['grouping_source.content_addon_record_id' => $source->getAttribute('content_addon_record_id')]);

        return true;
    }

    public function getOtherGroupedUsers(?User $currentUser = null): array
    {
        if (!$this->groupQuery) {
            return [];
        }

        if (!$this->_groupedUsers) {
            $this->_groupedUsers = User::find()->visible()
                ->leftJoin('activity', 'user.id=activity.created_by')
                ->andWhere(['activity.grouping_key' => $this->activity->record->grouping_key])
                // The activity's own author is named separately by the sentence
                // (`BaseActivity::formatDisplayNames()`), so they are not one of the "others" -
                // excluding only the activity RECORD would still list them again through any
                // second activity of theirs in the same group.
                ->andWhere(['!=', 'activity.created_by', $this->activity->user->id])
                ->andWhere(['!=', 'activity.created_by', $currentUser->id ?? 0])
                // One row per person, however many activities they contributed: the sentence
                // counts people ("and 3 more"), not activities.
                ->groupBy('user.id')
                ->orderBy([new Expression('MAX(activity.id) DESC')])
                ->limit(5)
                ->all();
        }

        return $this->_groupedUsers;
    }

    /**
     * After a new Activity was created OR the grouping of an existing Activity may changed.
     */
    public function afterInsert(): void
    {
        if ($this->needsGrouping()) {
            $subSelect = (clone $this->groupQuery)->select('activity.id')->createCommand()->getRawSql();
            Activity::updateAll(
                ['grouping_key' => $this->activity->record->id],
                // We need a "double" SubSelect to avoid MySQL Err: 1093
                new Expression('activity.id IN (SELECT id FROM (' . $subSelect . ') AS temp_tbl)'),
            );

            // Refresh for current activity
            $this->activity->record->refresh();
        }
    }

    /**
     * After an Activity was deleted
     */
    public function beforeDelete(): void
    {
        // Since we are not afterDelete, the record itself is not counted!
        $currentGroupCount = $this->getGroupCount();

        if ($currentGroupCount <= 1) {
            // Record was not grouped
            return;
        }

        if ($currentGroupCount < $this->threshold) {
            // We need to remove the current group, because it size is now to small to be grouped
            $this->destroyGroup();
        }
    }

    /**
     * After some special updates, like:
     *
     * - Visibility changed
     * - Content moved to another container
     *
     * We may need to upgrade grouping.
     */
    public function afterUpdate(): void
    {
        if (!$this->groupQuery) {
            return;
        }

        // We're not in a group, check for grouping
        if ($this->getGroupCount() <= 1) {
            $this->afterInsert();
            return;
        }

        // No longer in assigned group
        if (!$this->checkStillInCurrentGroup()) {
            $sibling = $this->getSiblingActivity();

            $this->activity->record->updateAttributes(['grouping_key' => $this->activity->record->id]);

            // Check if old group is large enough
            if (!$sibling->getGroupingService()->needsGrouping()) {
                $sibling->getGroupingService()->destroyGroup();
            }

            $this->afterInsert();
        }
    }

    /**
     * Checks if the current activity is still in the current group.
     * Query another group member and check if we're still a sibiling.
     *
     * @return bool
     */
    private function checkStillInCurrentGroup(): bool
    {
        return $this->getSiblingActivity()->getGroupingService()->hasSibling($this->activity->record->id) ?? false;
    }

    public function hasSibling(int $id): bool
    {
        $query = clone $this->groupQuery;
        return $query->andWhere(['activity.id' => $id])->exists();
    }

    private function getGroupCount(): int
    {
        return Activity::find()
            ->andWhere(['grouping_key' => $this->activity->record->grouping_key])
            ->count();
    }

    private function needsGrouping(): bool
    {
        return (
            $this->groupQuery
            && $this->groupQuery->count() >= $this->threshold
        );
    }

    private function getSiblingActivity(): ?BaseActivity
    {
        if (!$this->_sibling) {
            // Returns the first next activity in the current Activity group
            $record = Activity::find()
                ->andWhere(['grouping_key' => $this->activity->record->grouping_key])
                ->andWhere(['!=', 'activity.id', $this->activity->record->id])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])->one();
            if ($record) {
                $this->_sibling = ActivityManager::load($record);
            }
        }

        return $this->_sibling;
    }

    private function destroyGroup(): void
    {
        Activity::updateAll(
            ['grouping_key' => new Expression('id')],
            ['grouping_key' => $this->activity->record->grouping_key],
        );
    }

}

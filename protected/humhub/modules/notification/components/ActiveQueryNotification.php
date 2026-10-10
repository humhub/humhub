<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Query of {@see Notification} records.
 *
 * `seen()`/`unseen()` may be called before or after `grouped()`: the condition is applied when
 * the query is built, as a WHERE on single rows or as a HAVING on groups.
 *
 * @method Notification[] all($db = null)
 * @method Notification|null one($db = null)
 *
 * @since 1.20
 */
class ActiveQueryNotification extends ActiveQuery
{
    private bool $grouped = false;

    /**
     * @var bool|null true: only unseen rows/groups, false: only seen rows/groups, null: no filter
     */
    private ?bool $unseenFilter = null;

    public function forUser(User|int $user): static
    {
        return $this->andWhere(['notification.user_id' => $user instanceof User ? $user->id : $user]);
    }

    /**
     * Rows that appear in the web list.
     */
    public function listed(): static
    {
        return $this->andWhere(['notification.listed' => 1]);
    }

    /**
     * Rows (or, with {@see grouped()}, groups) with an unseen member.
     */
    public function unseen(): static
    {
        $this->unseenFilter = true;
        return $this;
    }

    /**
     * Rows (or, with {@see grouped()}, groups) entirely seen.
     */
    public function seen(): static
    {
        $this->unseenFilter = false;
        return $this;
    }

    /**
     * One row per group - the group's newest id in `group_max_id`, its size in `group_count`,
     * 1 in `group_unseen` when any member is unseen - newest group first.
     */
    public function grouped(): static
    {
        $this->grouped = true;

        return $this
            ->addSelect([
                'notification.*',
                'count(*) as group_count',
                'max(notification.id) as group_max_id',
                new Expression('MAX(notification.seen_at IS NULL) as group_unseen'),
            ])
            ->addGroupBy('notification.grouping_key')
            ->orderBy(['notification.grouping_key' => SORT_DESC]);
    }

    /**
     * Rows created in the time bucket of the given length that contains the given date.
     */
    public function timeBucket(int $bucketIntervalSeconds, string|\DateTimeInterface $dateTime): static
    {
        if ($bucketIntervalSeconds <= 0) {
            throw new \InvalidArgumentException('BucketIntervalSeconds must be greater than 0.');
        }

        if ($dateTime instanceof \DateTimeInterface) {
            $dt = ($dateTime instanceof \DateTime)
                ? \DateTimeImmutable::createFromMutable($dateTime)
                : $dateTime;
        } else {
            try {
                $dt = new \DateTimeImmutable($dateTime);
            } catch (\Exception) {
                throw new \InvalidArgumentException("Invalid date time given: '{$dateTime}'");
            }
        }

        $startTs = (int)(floor($dt->getTimestamp() / $bucketIntervalSeconds) * $bucketIntervalSeconds);
        $startDateTime = $dt->setTimestamp($startTs);

        $endDateTime = $startDateTime->modify("+{$bucketIntervalSeconds} seconds");

        return $this->andWhere([
            'AND',
            ['>=', 'notification.created_at', $startDateTime->format('Y-m-d H:i:s')],
            ['<', 'notification.created_at', $endDateTime->format('Y-m-d H:i:s')],
        ]);
    }

    /**
     * Applies the seen/unseen filter to the query built by the parent (a new instance), so building
     * the query twice does not stack it.
     *
     * @inheritdoc
     */
    public function prepare($builder)
    {
        $query = parent::prepare($builder);

        if ($this->unseenFilter === null) {
            return $query;
        }

        if ($this->grouped) {
            $query->andHaving(['group_unseen' => $this->unseenFilter ? 1 : 0]);
        } elseif ($this->unseenFilter) {
            $query->andWhere(['notification.seen_at' => null]);
        } else {
            $query->andWhere(['IS NOT', 'notification.seen_at', null]);
        }

        return $query;
    }
}

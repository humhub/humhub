<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\jobs;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\queue\LongRunningActiveJob;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Yii;

/**
 * Send Bulk Notification
 *
 * @author buddha
 * @since 1.2
 */
class SendBulkNotification extends LongRunningActiveJob
{
    /**
     * @var BaseNotification Base notification object
     */
    public $notification;

    /**
     * @var ActiveQueryUser the query to determine which users should receive this notification
     */
    public $query;

    /**
     * @var string|null $uid Unique ID to cache already processed user IDs
     * @since 1.17.3
     */
    public ?string $uid = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->uid === null) {
            $this->uid = Yii::$app->security->generateRandomString();
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        Yii::$app->notification->sendBulk($this);
    }

    /**
     * Get a cache key for processed user IDs
     *
     * @return string
     * @since 1.17.3
     */
    private function getProcessedCacheKey(): string
    {
        return 'SendBulkNotification' . $this->uid;
    }

    /**
     * Get the array with processed user IDs
     *
     * @return array
     * @since 1.17.3
     */
    public function getProcessedUserIds(): array
    {
        $processedUserIds = Yii::$app->cache->get($this->getProcessedCacheKey());
        return is_array($processedUserIds) ? $processedUserIds : [];
    }

    /**
     * Check if the user has been already processed
     *
     * @param User $user
     * @return bool
     * @since 1.17.3
     */
    public function isProcessedUser(User $user): bool
    {
        return in_array($user->id, $this->getProcessedUserIds());
    }

    /**
     * Mark the user as processed to avoid duplicated notifications
     *
     * @param User $user
     * @return void
     * @since 1.17.3
     */
    public function acknowledge(User $user): void
    {
        $processedUserIds = $this->getProcessedUserIds();
        $processedUserIds[] = $user->id;
        Yii::$app->cache->set($this->getProcessedCacheKey(), $processedUserIds);
    }

    /**
     * Get users query with excluding already processed users
     *
     * @return ActiveQueryUser
     * @since 1.17.3
     */
    public function getQuery(): ActiveQueryUser
    {
        $processedUserIds = $this->getProcessedUserIds();
        if ($processedUserIds !== []) {
            $this->query->andWhere(['NOT IN', 'user.id', $processedUserIds]);
        }

        return $this->query;
    }
}

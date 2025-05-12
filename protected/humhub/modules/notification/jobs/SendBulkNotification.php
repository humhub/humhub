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
use yii\db\Query;
use yii\queue\db\Queue;

/**
 * Description of SendNotification
 *
 * @author buddha
 * @since 1.2
 */
class SendBulkNotification extends LongRunningActiveJob
{
    /**
     * @var BaseNotification Base notification data as array.
     */
    public $notification;

    /**
     * @var ActiveQueryUser the query to determine which users should receive this notification
     */
    public $query;

    /**
     * @var int[] Ids of the processed used to avoid duplicated notifications
     * @since 1.17.3
     */
    public array $processed = [];

    /**
     * @var int|string|null $uid Unique ID or Queue ID
     * @since 1.17.3
     */
    public $uid = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->uid === null) {
            // Generate unique key only for new record, it is used to find a queue record of this job in DB
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
     * Check if the user has been already processed
     *
     * @param User $user
     * @return bool
     * @since 1.17.3
     */
    public function isProcessedUser(User $user): bool
    {
        return in_array($user->id, $this->processed);
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
        $this->processed[] = $user->id;
        $this->updateInQueue();
    }

    /**
     * Update this notification in queue db record
     *
     * @return void
     * @since 1.17.3
     */
    protected function updateInQueue(): void
    {
        $queueId = $this->getQueueId();
        if (!is_int($queueId) || $queueId === 0) {
            return;
        }

        // New processed user ID and/or real queue ID may be stored here
        Yii::$app->queue->db->createCommand()
            ->update(
                Yii::$app->queue->tableName,
                ['job' => Yii::$app->queue->serializer->serialize($this)],
                ['id' => $queueId],
            )->execute();
    }

    /**
     * Get ID of the queue db record where the notification is called from
     *
     * @return int|null
     * @since 1.17.3
     */
    protected function getQueueId(): ?int
    {
        if (!is_int($this->uid) && Yii::$app->queue instanceof Queue) {
            // Try to find queue record by uid
            $queues = (new Query())->from(Yii::$app->queue->tableName);
            foreach ($queues->each() as $queue) {
                $job = Yii::$app->queue->serializer->unserialize($queue['job']);
                if ($job instanceof self && isset($job->uid) && $job->uid === $this->uid) {
                    $this->uid = $queue['id'];
                    break;
                }
            }
        }

        if (!is_int($this->uid)) {
            $this->uid = 0;
        }

        return $this->uid;
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\services;

use humhub\modules\notification\jobs\DeliverJob;
use humhub\modules\notification\models\NotificationDelivery;
use Yii;

/**
 * Maintenance of the delivery layer, run by the hourly cron
 * ({@see \humhub\modules\notification\Events::onCronHourlyRun()}).
 *
 * @internal
 * @since 1.20
 */
class DeliveryService
{
    /**
     * @var int seconds a pending delivery must be overdue before {@see sweep()} pushes a job for it
     */
    public const OVERDUE_SECONDS = 300;

    /**
     * @var int days the finished (sent, skipped, failed) rows are kept for diagnostics
     */
    public const RETENTION_DAYS = 30;

    /**
     * The safety net of the {@see DeliverJob}s: pushes one for every recipient and channel with a
     * pending delivery overdue by more than {@see OVERDUE_SECONDS} - which covers a lost queue
     * job - and deletes the finished rows older than {@see RETENTION_DAYS}. Pending rows stay
     * until delivered; they go with their notification through the foreign key.
     *
     * @return int the number of pushed jobs
     */
    public function sweep(): int
    {
        $now = DeliveryScheduler::now();

        $overdue = NotificationDelivery::find()
            ->select(['notification_delivery.user_id', 'notification_delivery.channel'])
            ->distinct()
            ->pending()
            ->dueBy($now - self::OVERDUE_SECONDS - 1)
            ->asArray()
            ->all();

        foreach ($overdue as $pair) {
            Yii::$app->queue->push(new DeliverJob(['userId' => (int)$pair['user_id'], 'channel' => $pair['channel']]));
        }

        NotificationDelivery::deleteAll([
            'and',
            ['state' => [NotificationDelivery::STATE_SENT, NotificationDelivery::STATE_SKIPPED, NotificationDelivery::STATE_FAILED]],
            ['<', 'created_at', date('Y-m-d H:i:s', $now - self::RETENTION_DAYS * 86400)],
        ]);

        return count($overdue);
    }
}

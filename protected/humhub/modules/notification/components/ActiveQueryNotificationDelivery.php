<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\modules\notification\models\NotificationDelivery;
use humhub\modules\user\models\User;
use yii\db\ActiveQuery;

/**
 * Query of {@see NotificationDelivery} rows. The scopes follow the
 * `(user_id, channel, state, due_at)` index.
 *
 * @method NotificationDelivery[] all($db = null)
 * @method NotificationDelivery|null one($db = null)
 *
 * @since 1.20
 */
class ActiveQueryNotificationDelivery extends ActiveQuery
{
    /**
     * The rows of one recipient and channel.
     */
    public function forUserAndChannel(User|int $user, string $channel): static
    {
        return $this->andWhere([
            'notification_delivery.user_id' => $user instanceof User ? $user->id : $user,
            'notification_delivery.channel' => $channel,
        ]);
    }

    public function pending(): static
    {
        return $this->andWhere(['notification_delivery.state' => NotificationDelivery::STATE_PENDING]);
    }

    /**
     * Rows due at the given time or earlier.
     *
     * @param int $timestamp
     */
    public function dueBy(int $timestamp): static
    {
        return $this->andWhere(['<=', 'notification_delivery.due_at', date('Y-m-d H:i:s', $timestamp)]);
    }
}

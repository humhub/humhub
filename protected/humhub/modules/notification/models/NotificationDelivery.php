<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\models;

use humhub\components\ActiveRecord;
use humhub\modules\notification\components\ActiveQueryNotificationDelivery;
use humhub\modules\user\models\User;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "notification_delivery": the delivery of one notification
 * through one channel (e-mail, mobile push, a module's), see
 * {@see \humhub\modules\notification\services\DeliveryScheduler} and
 * {@see \humhub\modules\notification\jobs\DeliverJob}.
 *
 * The web list has no rows: the notification record itself is its delivery. A row goes from
 * pending to sent, skipped (seen meanwhile, channel switched off, recipient online, notification
 * no longer loadable) or failed (the channel threw); the states are kept for 30 days for
 * diagnostics, see {@see \humhub\modules\notification\services\DeliveryService::sweep()}.
 *
 * @property int $id
 * @property int $notification_id
 * @property int $user_id the recipient, denormalized for the per-user queries
 * @property string $channel the id of the target, e.g. `email`
 * @property int $state one of the `STATE_*` constants
 * @property string $due_at when the delivery is to go out
 * @property string|null $sent_at
 * @property string $created_at
 *
 * @property-read Notification $notification
 * @property-read User $user
 *
 * @since 1.20
 */
class NotificationDelivery extends ActiveRecord
{
    public const STATE_PENDING = 0;
    public const STATE_SENT = 1;
    public const STATE_SKIPPED = 2;
    public const STATE_FAILED = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notification_delivery';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['notification_id', 'user_id', 'channel', 'due_at'], 'required'],
            [['notification_id', 'user_id', 'state'], 'integer'],
            [['state'], 'in', 'range' => [self::STATE_PENDING, self::STATE_SENT, self::STATE_SKIPPED, self::STATE_FAILED]],
            [['channel'], 'string', 'max' => 32],
            [['due_at', 'sent_at'], 'safe'],
        ];
    }

    public function getNotification(): ActiveQuery
    {
        return $this->hasOne(Notification::class, ['id' => 'notification_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return ActiveQueryNotificationDelivery
     */
    public static function find(): ActiveQueryNotificationDelivery
    {
        return new ActiveQueryNotificationDelivery(static::class);
    }
}

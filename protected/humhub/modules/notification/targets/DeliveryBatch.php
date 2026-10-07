<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\services\NotificationListService;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Url;

/**
 * One message of a channel: one or several notifications of one recipient, handed to
 * {@see BaseTarget::deliver()}.
 *
 * @since 1.20
 */
final readonly class DeliveryBatch
{
    /**
     * @param BaseNotification[] $notifications one or several, all for $recipient
     * @param string $channel the id of the target the batch is delivered through
     */
    public function __construct(
        public User $recipient,
        public array $notifications,
        public string $channel,
    ) {
        if ($notifications === []) {
            throw new InvalidArgumentException('A delivery batch needs at least one notification.');
        }
    }

    public function isSingle(): bool
    {
        return count($this->notifications) === 1;
    }

    /**
     * One: the notification's own subject (one line); several: "{count} new notifications".
     */
    public function getSubject(): string
    {
        if ($this->isSingle()) {
            return preg_replace('/\s*\R\s*/', ' ', trim($this->first()->getMailSubject()));
        }

        return Yii::t(
            'NotificationModule.base',
            '{count,plural,one{# new notification} other{# new notifications}}',
            ['count' => count($this->notifications)],
        );
    }

    /**
     * One: {@see BaseNotification::asPush()}; several: the subject.
     */
    public function getPushBody(): string
    {
        return $this->isSingle() ? $this->first()->asPush() : $this->getSubject();
    }

    /**
     * One: the entry URL; several: the notification overview, absolute.
     */
    public function getUrl(): string
    {
        return $this->isSingle() ? $this->first()->getEntryUrl() : Url::to(['/notification/overview'], true);
    }

    /**
     * Identifies what a newer push message may replace on the device.
     *
     * One notification: `<channel>:<recipient id>:<grouping key>` - a newer message about the same
     * group ("Jane and 3 more liked …") replaces the older one, while unrelated notifications stay
     * side by side. Several: `<channel>:<recipient id>` - a newer summary replaces the older one.
     */
    public function getCollapseKey(): string
    {
        $key = $this->channel . ':' . $this->recipient->id;

        return $this->isSingle() ? $key . ':' . (int)$this->first()->record->grouping_key : $key;
    }

    /**
     * Whether any notification has the high priority.
     */
    public function isHighPriority(): bool
    {
        foreach ($this->notifications as $notification) {
            if ((int)$notification->record->priority === NotificationPriority::High->value) {
                return true;
            }
        }

        return false;
    }

    /**
     * The recipient's number of unseen entries in the web list (groups count once), see
     * {@see NotificationListService::unseenCount()}.
     */
    public function getUnreadCount(): int
    {
        return NotificationListService::unseenCount($this->recipient);
    }

    /**
     * The first notification of the batch - the only one of a single batch.
     *
     * @return BaseNotification
     */
    public function first(): BaseNotification
    {
        return $this->notifications[array_key_first($this->notifications)];
    }
}

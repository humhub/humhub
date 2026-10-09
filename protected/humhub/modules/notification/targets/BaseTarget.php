<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\user\models\User;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * A channel notifications reach their recipients through - the web list, e-mail, mobile push or
 * a channel a module adds (e.g. a chat integration).
 *
 * A target is configured by its {@see $id} in the `targets` of the `notification` component
 * (`config/common.php`). Each user switches the {@see NotificationCategory}s on or off per channel,
 * see {@see NotificationSettingsService}; a notification reaches the user through the channel
 * when the target is active for them and its category is on ({@see isEnabled()}).
 * {@see deliver()} hands a {@see DeliveryBatch} - one or several notifications of one recipient -
 * to the channel.
 *
 * {@see $delays}, {@see $delayWindow}, {@see $lowPriorityDelay} and {@see $skipWhenOnline} are
 * read by the delivery layer: the {@see \humhub\modules\notification\services\DeliveryScheduler}
 * decides when a notification goes out, and the {@see \humhub\modules\notification\jobs\DeliverJob}
 * collects the recipient's pending notifications into one batch when one of them is due.
 * `delays = [0]` makes a channel instant. With a queue that does not honour the delay of a job
 * (e.g. the `Instant` and `Sync` drivers) every delay is 0, see
 * {@see \humhub\modules\notification\services\DeliveryScheduler::isInstant()}.
 *
 * @api for channel providers
 * @since 1.2, rewritten in 1.20
 */
abstract class BaseTarget extends BaseObject
{
    /**
     * @var string unique id, also the key of the target in the `notification` component config and in the settings
     */
    public string $id = '';

    /**
     * @var bool whether the target is used at all; e.g. switched off in the configuration
     * @since 1.4
     */
    public bool $active = true;

    /**
     * @var int[] seconds the delivery layer waits with the 1st, 2nd, … message within the
     * {@see $delayWindow}; the last value applies to every further message. A high-priority
     * notification never waits.
     * @since 1.20
     */
    public array $delays = [0, 300, 900, 1800];

    /**
     * @var int seconds the messages counted for {@see $delays} reach back
     * @since 1.20
     */
    public int $delayWindow = 3600;

    /**
     * @var int seconds a low-priority notification waits at least - it usually goes along with an
     * earlier message, which takes all pending notifications of the channel
     * @since 1.20
     */
    public int $lowPriorityDelay = 1800;

    /**
     * @var bool whether the delivery layer sends nothing while the recipient was active on the site
     * within the last minute (and sees the notification in the web list), whether or not their
     * online status is displayed: notifications already due are skipped, not postponed; those not
     * yet due stay pending
     * @since 1.20
     */
    public bool $skipWhenOnline = false;

    /**
     * @inheritdoc
     * @throws InvalidConfigException without an id - the settings keys and the collapse key need one
     */
    public function init()
    {
        parent::init();

        if ($this->id === '') {
            throw new InvalidConfigException('The notification target ' . static::class . ' has no id.');
        }
    }

    /**
     * @return string the human-readable name of the channel
     */
    abstract public function getTitle(): string;

    /**
     * Sends the batch through the channel.
     *
     * @since 1.20
     */
    abstract public function deliver(DeliveryBatch $batch): void;

    /**
     * Whether the target is available - for the given user, or globally without a user. A subclass
     * may require e.g. an installed provider; it always checks `parent::isActive()`.
     */
    public function isActive(?User $user = null): bool
    {
        return $this->active;
    }

    /**
     * Whether the channel can carry notifications of the category at all - every category by default.
     * The settings page offers a switch only for the categories a channel applies to.
     *
     * @since 1.20
     */
    public function appliesTo(NotificationCategory $category): bool
    {
        return true;
    }

    /**
     * Whether notifications of this class reach the user through this channel: the target is
     * active and the class's category is switched on for it - or is not switchable.
     *
     * Without a user, the global defaults decide.
     *
     * @param class-string<BaseNotification> $notificationClass
     * @since 1.20
     */
    public function isEnabled(string $notificationClass, ?User $user = null): bool
    {
        if (!$this->isActive($user)) {
            return false;
        }

        return (new NotificationSettingsService($user))->isCategoryEnabled($this, $notificationClass::category());
    }
}

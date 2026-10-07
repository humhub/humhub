<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\user\models\User;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * A channel notifications reach their recipients through - the web list, e-mail, mobile push or
 * a channel a module adds (e.g. a chat integration).
 *
 * A target is configured by its {@see $id} in the `targets` of the `notification` component
 * (`config/common.php`). Each user chooses one of its {@see $modes} and switches the
 * {@see \humhub\modules\notification\components\NotificationGroup}s on or off per channel, see
 * {@see NotificationSettingsService}. {@see deliver()} hands a {@see DeliveryBatch} - one or
 * several notifications of one recipient - to the channel.
 *
 * {@see $delays}, {@see $delayWindow}, {@see $lowPriorityDelay} and {@see $skipWhenOnline} are
 * read by the delivery layer, which batches the notifications of a recipient. Until it exists
 * (phase 2), the dispatch job delivers every notification at once, one per batch, and these
 * properties have no effect.
 *
 * @since 1.2, rewritten in 1.20
 */
abstract class BaseTarget extends BaseObject
{
    public const MODE_ADAPTIVE = NotificationSettingsService::MODE_ADAPTIVE;
    public const MODE_SUMMARY = NotificationSettingsService::MODE_SUMMARY;
    public const MODE_OFF = NotificationSettingsService::MODE_OFF;

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
     * @var string[] the modes a user may choose; the first is the default
     * @since 1.20
     */
    public array $modes = [self::MODE_ADAPTIVE, self::MODE_OFF];

    /**
     * @var int[] read by the delivery layer: seconds to wait for the 1st, 2nd, … message within the
     * {@see $delayWindow}; the last value applies to every further message
     * @since 1.20
     */
    public array $delays = [0, 300, 900, 1800];

    /**
     * @var int read by the delivery layer: seconds the messages counted for {@see $delays} reach back
     * @since 1.20
     */
    public int $delayWindow = 3600;

    /**
     * @var int read by the delivery layer: seconds a low-priority notification waits for a message
     * that goes out anyway
     * @since 1.20
     */
    public int $lowPriorityDelay = 1800;

    /**
     * @var bool read by the delivery layer: whether nothing is sent while the recipient is online
     * (and sees the notification in the web list)
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
     * The user's mode of this channel, or the global default without a user.
     *
     * @since 1.20
     */
    public function getMode(?User $user = null): string
    {
        return (new NotificationSettingsService($user))->getMode($this);
    }

    /**
     * Whether notifications of this class reach the user through this channel: the target is
     * active, the mode is neither `off` nor `summary` (the summary mail carries those), and the
     * class's group is switched on - or is not switchable.
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

        $settings = new NotificationSettingsService($user);
        if (in_array($settings->getMode($this), [self::MODE_OFF, self::MODE_SUMMARY], true)) {
            return false;
        }

        return $settings->isGroupEnabled($this, $notificationClass::group());
    }
}

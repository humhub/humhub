<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification;

use humhub\modules\notification\components\NotificationPrivacy;
use yii\base\InvalidConfigException;

/**
 * Notification Module
 */
class Module extends \humhub\components\Module
{
    /**
     * The key of the administrator's privacy mode in the module's settings.
     *
     * @internal
     */
    public const SETTING_PRIVACY_MODE = 'privacyMode';

    /**
     * @var int Delete read notifications after 2 months(by default)
     */
    public $deleteSeenNotificationsMonths = 2;

    /**
     * @var int Delete unread notifications after 3 months(by default)
     */
    public $deleteUnseenNotificationsMonths = 3;

    /**
     * @var string|null the privacy mode of notification mails, push messages and the activity
     * summary mail - `full`, `redacted` or `minimal`, see {@see NotificationPrivacy}. Set in the
     * configuration file, it wins over the administrator's setting, which is then shown read-only;
     * `null` (the default) leaves it to the administrator.
     * @since 1.20
     */
    public ?string $privacyMode = null;

    /**
     * The privacy mode: {@see $privacyMode}, else the administrator's setting (also when fixed
     * through `fixed-settings`), else {@see NotificationPrivacy::Full}.
     *
     * @throws InvalidConfigException when {@see $privacyMode} is no valid mode
     * @internal use {@see NotificationPrivacy::current()}
     * @since 1.20
     */
    public function getPrivacy(): NotificationPrivacy
    {
        if ($this->privacyMode !== null) {
            return NotificationPrivacy::tryFrom($this->privacyMode)
                ?? throw new InvalidConfigException('Invalid privacyMode of the notification module: ' . $this->privacyMode);
        }

        return NotificationPrivacy::tryFrom((string)$this->settings->get(self::SETTING_PRIVACY_MODE))
            ?? NotificationPrivacy::Full;
    }

    /**
     * Whether the privacy mode is set in the configuration - {@see $privacyMode} or a fixed
     * setting - so the administrator cannot change it.
     *
     * @internal
     * @since 1.20
     */
    public function isPrivacyFixed(): bool
    {
        return $this->privacyMode !== null || $this->settings->isFixed(self::SETTING_PRIVACY_MODE);
    }

    /**
     * Stores the administrator's privacy mode; without effect while it {@see isPrivacyFixed()}.
     *
     * @internal
     * @since 1.20
     */
    public function setPrivacy(NotificationPrivacy $privacy): void
    {
        if (!$this->isPrivacyFixed()) {
            $this->settings->set(self::SETTING_PRIVACY_MODE, $privacy->value);
        }
    }
}

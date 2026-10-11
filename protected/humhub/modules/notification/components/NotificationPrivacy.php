<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\modules\notification\Module;
use Yii;

/**
 * How much the messages that leave HumHub show: notification mails, push messages and the
 * activity summary mail. The web list always shows everything.
 *
 * One level for the whole network, set by the administrator or in the configuration file, see
 * {@see current()} and `docs/admin/notifications.md`. The core renders the levels centrally -
 * a notification or activity class only offers a sentence without content,
 * {@see BaseNotification::getRedactedMessage()}.
 *
 * @api for reading
 * @since 1.20
 */
enum NotificationPrivacy: string
{
    /**
     * Everything: the sentence with names and content, the content blocks.
     */
    case Full = 'full';

    /**
     * Names but no content: a sentence without content
     * ({@see BaseNotification::getRedactedMessage()}, else "{displayName}: {category}"), no
     * content blocks, no space names; only a "View online" link.
     */
    case Redacted = 'redacted';

    /**
     * Neither names nor content: "New notification: {category}", counts per category, and a link.
     */
    case Minimal = 'minimal';

    /**
     * The level of the network: the `privacyMode` of the notification module's configuration,
     * else the administrator's setting, else {@see Full}.
     */
    public static function current(): self
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('notification');

        return $module->getPrivacy();
    }

    /**
     * Whether content is hidden - every level but {@see Full}.
     */
    public function isProtected(): bool
    {
        return $this !== self::Full;
    }
}

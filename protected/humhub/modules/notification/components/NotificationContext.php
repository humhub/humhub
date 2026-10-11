<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

/**
 * Where a notification is being rendered, handed to {@see BaseNotification::getBlocks()}.
 *
 * Constructed by the core only; more fields may be added in later versions, so read the
 * properties, do not construct it.
 *
 * @api for reading
 * @since 1.20
 */
final readonly class NotificationContext
{
    /**
     * @param string $channel the id of the target, e.g. `MailTarget::ID`
     * @param NotificationPrivacy $privacy how much the channel may show; the core renders the
     * levels itself - with any level but {@see NotificationPrivacy::Full} it shows no blocks
     * @internal
     */
    public function __construct(
        public string $channel,
        public NotificationPrivacy $privacy = NotificationPrivacy::Full,
    ) {
    }
}

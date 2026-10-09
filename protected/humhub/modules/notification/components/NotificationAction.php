<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

/**
 * An action a notification offers outside the web list, e.g. a button in the mail, see
 * {@see BaseNotification::getActions()}.
 *
 * @api
 * @since 1.20
 */
final readonly class NotificationAction
{
    /**
     * @param string $label the plain text label, encoded by the channel
     * @param string $url an absolute URL
     */
    public function __construct(
        public string $label,
        public string $url,
    ) {
    }
}

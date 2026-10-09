<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use humhub\components\message\MessageParam;
use humhub\modules\notification\components\BaseNotification;

/**
 * Adds the `spaceName` message parameter - the name of {@see BaseNotification::getSpace()} - to
 * the space notifications.
 *
 * @mixin BaseNotification
 * @internal
 * @since 1.20
 */
trait SpaceNotificationTrait
{
    /**
     * @inheritdoc
     */
    protected function getMessageParams(): array
    {
        return ['spaceName' => MessageParam::emphasis($this->getSpace()?->displayName ?? '')];
    }
}

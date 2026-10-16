<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use RuntimeException;

/**
 * Fails to render for user 3.
 */
final class TestThrowingNotification extends BaseNotification
{
    public static function category(): NotificationCategory
    {
        return NotificationCategory::social();
    }

    protected function getMessage(array $params): string
    {
        if ((int)$this->recipient->id === 3) {
            throw new RuntimeException('Rendering failed for user 3');
        }

        return "{$params['displayName']} did a thing";
    }
}

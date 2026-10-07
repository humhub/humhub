<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use RuntimeException;

/**
 * Fails to render for user 3.
 */
final class TestThrowingNotification extends BaseNotification
{
    public static function group(): NotificationGroup
    {
        return NotificationGroup::social();
    }

    protected function getMessage(array $params): string
    {
        if ((int)$this->recipient->id === 3) {
            throw new RuntimeException('Rendering failed for user 3');
        }

        return "{$params['displayName']} did a thing";
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;

final class TestDirectNotification extends BaseNotification
{
    public static function group(): NotificationGroup
    {
        return NotificationGroup::direct();
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} mentioned you";
    }
}

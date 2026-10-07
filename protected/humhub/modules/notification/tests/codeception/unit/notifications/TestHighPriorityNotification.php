<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\components\NotificationPriority;

final class TestHighPriorityNotification extends BaseNotification
{
    public static function group(): NotificationGroup
    {
        return NotificationGroup::social();
    }

    public static function priority(): NotificationPriority
    {
        return NotificationPriority::High;
    }

    public static function listed(): bool
    {
        return false;
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} did something important";
    }
}

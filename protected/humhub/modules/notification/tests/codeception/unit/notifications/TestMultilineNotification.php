<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;

final class TestMultilineNotification extends BaseNotification
{
    public static function category(): NotificationCategory
    {
        return NotificationCategory::social();
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} wrote\r\n  two\n\nlines ";
    }
}

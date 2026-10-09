<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;

/**
 * A notification of a module's own group.
 */
final class TestModuleCategoryNotification extends BaseNotification
{
    public static function category(): NotificationCategory
    {
        return new NotificationCategory('example-reports', 'Reports', sortOrder: 350);
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} reported something";
    }
}

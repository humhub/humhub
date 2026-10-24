<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\NotificationCategory;

/**
 * Groups by the {@see $grouping} a test sets.
 */
final class TestConfigurableGroupingNotification extends BaseNotification
{
    public static ?Grouping $grouping = null;

    public static function category(): NotificationCategory
    {
        return NotificationCategory::social();
    }

    public static function grouping(): ?Grouping
    {
        return self::$grouping;
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} did a thing";
    }
}

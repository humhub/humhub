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

final class TestGroupedNotification extends BaseNotification
{
    use TestPriorityTrait;

    public static function category(): NotificationCategory
    {
        return NotificationCategory::social();
    }

    public static function grouping(): ?Grouping
    {
        return Grouping::byClass()->andContent();
    }

    protected function getMessage(array $params): string
    {
        return $this->groupCount > 1
            ? "{$params['displayNames']} did {$params['groupCount']} things"
            : "{$params['displayName']} did a thing";
    }
}

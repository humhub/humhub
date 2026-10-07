<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;

final class TestNotification extends BaseNotification
{
    public static function group(): NotificationGroup
    {
        return NotificationGroup::social();
    }

    protected function getMessage(array $params): string
    {
        return $this->groupCount > 1
            ? "{$params['displayNames']} did {$params['groupCount']} things"
            : "{$params['displayName']} did a thing";
    }
}

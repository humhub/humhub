<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\NotificationPriority;

/**
 * Lets a delivery test pick the priority of a test notification class for the next dispatches,
 * see {@see \humhub\modules\notification\tests\codeception\unit\DeliveryTestTrait::dispatchToAdmin()}.
 */
trait TestPriorityTrait
{
    public static ?NotificationPriority $testPriority = null;

    public static function priority(): NotificationPriority
    {
        return static::$testPriority ?? parent::priority();
    }
}

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
 * Fails on demand after it was dispatched: in {@see group()} (so `isEnabled()` throws) or while
 * it is constructed.
 */
final class TestFlakyNotification extends BaseNotification
{
    use TestPriorityTrait;

    public static bool $failCategory = false;

    public static bool $failInit = false;

    public static function category(): NotificationCategory
    {
        if (self::$failCategory) {
            throw new RuntimeException('Category lookup failed');
        }

        return NotificationCategory::content();
    }

    public function init()
    {
        parent::init();

        if (self::$failInit) {
            throw new RuntimeException('Construction failed');
        }
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} did a flaky thing";
    }
}

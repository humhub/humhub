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
 * A notification that always goes out as a message of its own, like a news article.
 */
final class TestStandaloneNotification extends BaseNotification
{
    use TestPriorityTrait;

    public static function category(): NotificationCategory
    {
        return NotificationCategory::social();
    }

    public static function standalone(): bool
    {
        return true;
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} published an article";
    }
}

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
 * A notification whose mail body is the payload's `body`.
 */
final class TestExcerptNotification extends BaseNotification
{
    public static function category(): NotificationCategory
    {
        return NotificationCategory::direct();
    }

    public function getExcerpt(): ?string
    {
        return $this->payload['body'] ?? null;
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} wrote you";
    }
}

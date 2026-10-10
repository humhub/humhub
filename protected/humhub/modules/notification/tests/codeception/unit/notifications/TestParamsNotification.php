<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\components\message\MessageParam;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;

final class TestParamsNotification extends BaseNotification
{
    public static function category(): NotificationCategory
    {
        return NotificationCategory::direct();
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} shared {$params['title']} ({$params['note']})";
    }

    protected function getMessageParams(): array
    {
        return [
            'title' => MessageParam::emphasis((string)$this->payload['title']),
            'note' => (string)$this->payload['note'],
        ];
    }
}

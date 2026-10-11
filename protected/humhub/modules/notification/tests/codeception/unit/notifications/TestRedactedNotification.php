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
use humhub\modules\user\models\User;

/**
 * Has a sentence without content and records the parameters it gets; its own parameters are a
 * title, a note and a user.
 */
final class TestRedactedNotification extends BaseNotification
{
    public static array $redactedParams = [];

    public static function category(): NotificationCategory
    {
        return NotificationCategory::content();
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} shared {$params['title']} ({$params['note']}) with {$params['other']}: {$params['content']}";
    }

    protected function getMessageParams(): array
    {
        return [
            'title' => MessageParam::emphasis('Secret title'),
            'note' => 'Secret note',
            'other' => MessageParam::user(User::findOne(['id' => 3])),
        ];
    }

    protected function getRedactedMessage(array $params): ?string
    {
        self::$redactedParams = $params;

        return "{$params['displayName']} shared a {$params['contentType']} with {$params['other']}";
    }
}

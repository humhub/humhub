<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\user\models\User;
use RuntimeException;

/**
 * Rejects user 3 through {@see canReceive()}; throws there for the user id in the payload's `throwFor`.
 */
final class TestRejectingNotification extends BaseNotification
{
    public static function category(): NotificationCategory
    {
        return NotificationCategory::social();
    }

    public function canReceive(User $user): bool
    {
        if (($this->payload['throwFor'] ?? null) === (int)$user->id) {
            throw new RuntimeException('canReceive failed');
        }

        return (int)$user->id !== 3;
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} did a thing";
    }
}

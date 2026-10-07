<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;

/**
 * A notification whose mail body is the payload's `body`.
 */
final class TestMailBodyNotification extends BaseNotification
{
    public static function group(): NotificationGroup
    {
        return NotificationGroup::direct();
    }

    public function getMailBody(): ?string
    {
        return $this->payload['body'] ?? null;
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} wrote you";
    }
}

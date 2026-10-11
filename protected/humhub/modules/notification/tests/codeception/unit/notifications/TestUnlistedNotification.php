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
 * A notification of a module's own group that never appears in the web list, like a messenger's.
 */
final class TestUnlistedNotification extends BaseNotification
{
    public static function category(): NotificationCategory
    {
        return new NotificationCategory('example-chat', 'Chat', 'New messages', sortOrder: 50);
    }

    public static function listed(): bool
    {
        return false;
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} wrote something";
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use humhub\modules\notification\components\BaseNotification;

final class TestDefaultCategoryNotification extends BaseNotification
{
    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} did a module thing";
    }
}

<?php

namespace humhub\modules\notification\tests\codeception\unit\rendering\notifications;

use humhub\modules\notification\components\BaseNotification;

/**
 * Notification without html() or text() overrides to test default rendering.
 */
class PlainTestNotification extends BaseNotification
{
    public $moduleId = 'notification';
    public $requireOriginator = false;
    public $requireSource = false;
}

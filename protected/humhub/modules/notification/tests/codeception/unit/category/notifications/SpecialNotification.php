<?php

namespace humhub\modules\notification\tests\codeception\unit\category\notifications;

use humhub\modules\notification\components\BaseNotification;

/**
 * Description of TestedDefaultViewNotification
 *
 * @author buddha
 */
class SpecialNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public function category()
    {
        return new SpecialNotificationCategory();
    }
}

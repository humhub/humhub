<?php

namespace humhub\modules\notification\tests\codeception\unit\category\notifications;

/**
 * Description of TestedDefaultViewNotification
 *
 * @author buddha
 */
class SpecialNotification extends \humhub\modules\notification\components\BaseNotification
{
    
    /**
     * @inheritdoc
     */
    public function category()
    {
        return new SpecialNotificationCategory();
    }
}

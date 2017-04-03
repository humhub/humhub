<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\modules\user\models\User;
use humhub\modules\notification\components\BaseNotification;

/**
 * Mobile Target
 * 
 * @since 1.2
 * @author buddha
 */
class MobileTarget extends BaseTarget
{

    /**
     * @inheritdoc
     */
    public $id = 'mobile';

    /**
     * Used to forward a BaseNotification object to a BaseTarget.
     * The notification target should handle the notification by pushing a Job to
     * a Queue or directly handling the notification.
     * 
     * @param BaseNotification $notification
     */
    public function handle(BaseNotification $notification, User $user)
    {
        
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        
    }

}

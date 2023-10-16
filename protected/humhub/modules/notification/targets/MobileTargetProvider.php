<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
namespace humhub\modules\notification\targets;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\user\models\User;

interface MobileTargetProvider
{
    /**
     * @param BaseNotification $notification
     * @param User $user
     * @return boolean
     */
    public function handle(BaseNotification $notification, User $user);
    /**
     * @param User|null $user
     * @return boolean
     */
    public function isActive(User $user = null);
}

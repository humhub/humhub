<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\MobileTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use Yii;

class SpaceCreatedNotificationCategory extends NotificationCategory
{
    /**
     * @inheritdoc
     */
    public $id = "space_created";

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return Yii::t('SpaceModule.notification', 'Receive Notifications when a new Space is created by a non-space manager.');
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return Yii::t('SpaceModule.notification', 'New Space');
    }

    /**
     * @inheritdoc
     */
    public function getDefaultSetting(BaseTarget $target)
    {
        switch ($target->id) {
            case MailTarget::getId():
            case WebTarget::getId():
            case MobileTarget::getId():
                return true;
            default:
                return $target->defaultSetting;
        }
    }

    /**
     * @inerhitdoc
     */
    public function isVisible(User $user = null)
    {
        return $user && (new PermissionManager(['subject' => $user]))->can(ManageSpaces::class);
    }
}

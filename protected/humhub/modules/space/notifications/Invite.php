<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;

/**
 * If an user was invited to a workspace, this notification is fired.
 *
 * @since 0.5
 */
class Invite extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = "space";

    /**
     * @inheritdoc
     */
    public $viewName = "invite";

    /**
     * @inheritdoc
     */
    public $markAsSeenOnClick = false;

    /**
     *  @inheritdoc
     */
    public function category()
    {
        return new SpaceMemberNotificationCategory;
    }
    
    /**
     *  @inheritdoc
     */
    public function getSpace()
    {
        return $this->source;
    }

    /**
     *  @inheritdoc
     */
    public function getTitle(\humhub\modules\user\models\User $user)
    {
        return Yii::t('SpaceModule.notification', '{displayName} just invited you to the space {spaceName}', array(
                    '{displayName}' => Html::encode($this->originator->displayName),
                    '{spaceName}' => Html::encode($this->getSpace()->name)
        ));
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return Yii::t('SpaceModule.notification', '{displayName} invited you to the space {spaceName}', array(
                    '{displayName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    '{spaceName}' => Html::tag('strong', Html::encode($this->getSpace()->name))
        ));
    }

}

?>

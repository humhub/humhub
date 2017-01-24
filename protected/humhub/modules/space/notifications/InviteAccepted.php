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
 * SpaceInviteAcceptedNotification is sent to the originator of the invite to
 * inform him about the accept.
 *
 * @since 0.5
 * @author Luke
 */
class InviteAccepted extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = "space";
    
    /**
     * @inheritdoc
     */
    public $viewName = "inviteAccepted";
    
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
    public function getTitle(\humhub\modules\user\models\User $user)
    {
        return Yii::t('SpaceModule.notification', '{displayName} accepted your invite for the space {spaceName}', [
                    '{displayName}' => Html::encode($this->originator->displayName),
                    '{spaceName}' => Html::encode($this->source->name)
        ]);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return Yii::t('SpaceModule.notification', '{displayName} accepted your invite for the space {spaceName}', [
                    '{displayName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    '{spaceName}' => Html::tag('strong', Html::encode($this->source->name))
        ]);
    }

}

?>

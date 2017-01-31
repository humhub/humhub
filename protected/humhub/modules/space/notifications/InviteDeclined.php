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
 * SpaceInviteDeclinedNotification is sent to the originator of the invite to
 * inform him about the decline.
 *
 * @since 0.5
 * @author Luke
 */
class InviteDeclined extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = "space";

    /**
     * @inheritdoc
     */
    public $viewName = "inviteDeclined";

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

    public function getTitle(\humhub\modules\user\models\User $user)
    {
        return Yii::t('SpaceModule.notification', '{displayName} declined your invite for the space {spaceName}', [
                    '{displayName}' => Html::encode($this->originator->displayName),
                    '{spaceName}' => Html::encode($this->getSpace()->name)
        ]);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return Yii::t('SpaceModule.notification', '{displayName} declined your invite for the space {spaceName}', [
                    '{displayName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    '{spaceName}' => Html::tag('strong', Html::encode($this->getSpace()->name))
        ]);
    }

}

?>

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
 * SpaceApprovalRequestNotification
 *
 * @since 0.5
 */
class ApprovalRequest extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = "space";

    /**
     * @inheritdoc
     */
    public $viewName = "approval";
    public $message;

    /**
     * @inheritdoc
     */
    public $markAsSeenOnClick = false;

    /**
     * Sets the approval request message for this notification.
     * 
     * @param string $message
     */
    public function withMessage($message)
    {
        if ($message) {
            $this->message = $message;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getViewParams($params = array())
    {
        return \yii\helpers\ArrayHelper::merge(parent::getViewParams(['message' => $this->message]), $params);
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        return Yii::t('SpaceModule.notification', '{displayName} requests membership for the space {spaceName}', [
                    '{displayName}' => Html::encode($this->originator->displayName),
                    '{spaceName}' => Html::encode($this->source->name)
        ]);
    }

    /**
     *  @inheritdoc
     */
    public function category()
    {
        return new SpaceMemberNotificationCategory;
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return Yii::t('SpaceModule.notification', '{displayName} requests membership for the space {spaceName}', [
                    '{displayName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    '{spaceName}' => Html::tag('strong', Html::encode($this->source->name))
        ]);
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize(['source' => $this->source, 'originator' => $this->originator, 'message' => $this->message]);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->init();
        $unserializedArr = unserialize($serialized);
        $this->from($unserializedArr['originator']);
        $this->about($unserializedArr['source']);
        $this->withMessage($unserializedArr['message']);
    }

}

?>

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\notifications;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;

/**
 * Friends Request
 *
 * @since 1.1
 */
class Request extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = "friendship";

    /**
     * @inheritdoc
     */
    public $markAsSeenOnClick = false;

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->originator->getUrl();
    }

    /**
     * @inheritdoc
     */
    public static function getTitle()
    {
        return Yii::t('FriendshipModule.notifications_Request', 'Friendship Request');
    }

    /**
     * @inheritdoc
     */
    public function getAsHtml()
    {
        return Yii::t('FriendshipModule.notification', '{displayName} sent you a friend request.', array(
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
        ));
    }

}

?>

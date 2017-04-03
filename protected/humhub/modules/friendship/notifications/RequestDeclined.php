<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\notifications;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;

/**
 * Declined Friends Request Notification
 *
 * @since 1.1
 */
class RequestDeclined extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = "friendship";

    /**
     * @inheritdoc
     */
    public $viewName = "friendshipDeclined";

    /**
     * @inheritdoc
     */
    public $markAsSeenOnClick = true;

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new FriendshipNotificationCategory;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->originator->getUrl();
    }

    public function getMailSubject()
    {
        return strip_tags($this->html());
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return Yii::t('FriendshipModule.notification', '{displayName} declined your friend request.', [
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
        ]);
    }

}

?>

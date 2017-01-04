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
class RequestApproved extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = "friendship";

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

    /**
     * @inheritdoc
     */
    public function html()
    {
        return Yii::t('FriendshipModule.notification', '{displayName} accepted your friend request.', [
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
        ]);
    }

}

?>

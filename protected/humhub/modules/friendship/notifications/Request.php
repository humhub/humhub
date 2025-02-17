<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\notifications;

use humhub\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use Yii;

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
    public $viewName = 'friendshipRequest';

    /**
     * @inheritdoc
     */
    public $markAsSeenOnClick = false;

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->originator->getUrl(true);
    }

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new FriendshipNotificationCategory();
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        return $this->getInfoText($this->originator->displayName);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return $this->getInfoText(Html::tag('strong', Html::encode($this->originator->displayName)));
    }

    private function getInfoText($displayName)
    {
        return Yii::t('FriendshipModule.notification', '{displayName} sent you a friend request.', [
            'displayName' => $displayName,
        ]);
    }

}

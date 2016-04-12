<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\notifications;

use humhub\modules\notification\components\BaseNotification;
use Yii;

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
    public $viewName = "requestApproved";

    /**
     * @inheritdoc
     */
    public $markAsSeenOnClick = true;

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->originator->getUrl();
    }
    
    public static function getTitle()
    {
        return Yii::t('FriendshipModule.notifications_RequestApproved', 'Friendship Approved');
    }

}

?>

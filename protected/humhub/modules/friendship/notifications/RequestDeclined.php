<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\notifications;

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
    public $viewName = "requestDeclined";

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

}

?>

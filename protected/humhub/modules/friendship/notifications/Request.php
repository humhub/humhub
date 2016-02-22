<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\notifications;

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
    public $viewName = "request";

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

}

?>

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\notifications;

/**
 * ContentCreatedNotification is fired to all users which are manually selected
 * in ContentFormWidget to receive a notification.
 */
class ContentCreated extends \humhub\modules\notification\components\BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'content';

    /**
     * @inheritdoc
     */
    public $viewName = 'ContentCreated';

}

?>

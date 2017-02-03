<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\live;

use humhub\modules\live\components\LiveEvent;
use humhub\modules\content\models\Content;

/**
 * Live event for new notifications
 *
 * @since 1.2
 */
class NewNotification extends LiveEvent
{

    /**
     * @var int the id of the new notification
     */
    public $notificationId;
    
    /**
     * @var string text representation used for frotnend desktop notifications 
     */
    public $text;
    
    /**
     * @var int determines if desktop notification has already been sent. 
     */
    public $ts;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->visibility = Content::VISIBILITY_OWNER;
    }

}

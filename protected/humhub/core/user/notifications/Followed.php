<?php

namespace humhub\core\user\notifications;

use humhub\core\notification\components\BaseNotification;

/**
 * FollowNotification is fired to all users that are being 
 * followed by other user
 */
class Followed extends BaseNotification
{

    public $viewName = "follow";

}

?>

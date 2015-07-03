<?php

namespace humhub\modules\user\notifications;

use humhub\modules\notification\components\BaseNotification;

/**
 * FollowNotification is fired to all users that are being
 * followed by other user
 */
class Followed extends BaseNotification
{

    public $viewName = "follow";

}

?>

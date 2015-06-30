<?php

namespace humhub\core\like\notifications;

use humhub\core\notification\components\BaseNotification;

/**
 * Notifies a user about likes of his objects (posts, comments, tasks & co)
 *
 * @package humhub.modules_core.like.notifications
 * @since 0.5
 */
class NewLike extends BaseNotification
{

    public $viewName = "newLike";

}

?>

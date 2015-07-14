<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\notifications;

use humhub\modules\notification\components\BaseNotification;

/**
 * Notifies a user about likes of his objects (posts, comments, tasks & co)
 *
 * @since 0.5
 */
class NewLike extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'like';

    /**
     * @inheritdoc
     */
    public $viewName = "newLike";

}

?>

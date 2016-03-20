<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use humhub\modules\user\models\User;

/**
 * Notification for new comments
 *
 * @since 0.5
 */
class NewComment extends \humhub\modules\notification\components\BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'comment';

    /**
     * @inheritdoc
     */
    public $viewName = 'newComment';

    /**
     * @inheritdoc
     */
    public function send(User $user)
    {
        // Check there is also an mentioned notifications, so ignore this notification
        /*
          if (Notification::model()->findByAttributes(array('class' => 'MentionedNotification', 'source_object_model' => 'Comment', 'source_object_id' => $comment->id)) !== null) {
          continue;
          }
         */

        return parent::send($user);
    }

}

?>

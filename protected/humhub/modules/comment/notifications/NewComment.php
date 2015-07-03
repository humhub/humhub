<?php

namespace humhub\modules\comment\notifications;

/**
 * Notification for new comments
 *
 * @package humhub.modules_core.comment.notifications
 * @since 0.5
 */
class NewComment extends \humhub\modules\notification\components\BaseNotification
{

    /**
     * @inheritdoc
     */
    public $viewName = 'newComment';

    /**
     * @inheritdoc
     */
    public function send($user)
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

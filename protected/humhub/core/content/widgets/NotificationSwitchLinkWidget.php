<?php

/**
 * NotificationSwitch for Wall Entries
 *
 * This widget allows turn on/off of notifications of a content.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.10
 */
class NotificationSwitchLinkWidget extends HWidget
{

    /**
     * Content Object with SIContentBehaviour
     * @var type
     */
    public $content;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if (Yii::app()->user->isGuest) {
            return;
        }
        
        
        $this->render('notificationSwitchLink', array(
            'content' => $this->content,
            'state' => $this->content->isFollowedByUser(Yii::app()->user->id, true)
        ));
    }

}

?>
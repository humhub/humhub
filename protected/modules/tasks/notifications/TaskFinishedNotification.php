<?php

/**
 * TaskFinishedNotification is fired to the task creator after a task is finished.
 *
 * @author Luke
 */
class TaskFinishedNotification extends Notification {

    // Path to Web View of this Notification
    public $webView = "tasks.views.notifications.taskFinished";
    // Path to Mail Template for this notification
    public $mailView = "application.modules.tasks.views.notifications.taskFinished_mail";

}

?>

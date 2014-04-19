<?php

/**
 * TaskCreatedNotification is fired to the user which get a notification of creation.
 *
 * @author Luke
 */
class TaskCreatedNotification extends Notification {

    // Path to Web View of this Notification
    public $webView = "tasks.views.notifications.taskCreated";
    // Path to Mail Template for this notification
    public $mailView = "application.modules.tasks.views.notifications.taskCreated_mail";

}

?>

<?php

/**
 * TaskAssignedNotification is fired to the user which should handle this task.
 *
 * @author Luke
 */
class TaskAssignedNotification extends Notification {

    // Path to Web View of this Notification
    public $webView = "tasks.views.notifications.taskAssigned";
    // Path to Mail Template for this notification
    public $mailView = "application.modules.tasks.views.notifications.taskAssigned_mail";

}

?>

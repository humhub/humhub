<?php

/**
 * PollCreatedNotification is fired to the user who manually add to a poll for getting a notification.
 *
 * @author Andreas Strobel
 */
class PollCreatedNotification extends Notification {

    // Path to Web View of this Notification
    public $webView = "polls.views.notifications.PollCreated";
    // Path to Mail Template for this notification
    public $mailView = "application.modules.polls.views.notifications.PollCreated_mail";

}

?>

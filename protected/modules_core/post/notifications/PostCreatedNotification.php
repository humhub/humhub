<?php

/**
 * PostCreatedNotification is fired to the user which add manually to a post for getting a notification.
 *
 * @author Luke
 */
class PostCreatedNotification extends Notification {

    // Path to Web View of this Notification
    public $webView = "post.views.notifications.PostCreated";
    // Path to Mail Template for this notification
    public $mailView = "application.modules_core.post.views.notifications.PostCreated_mail";

}

?>

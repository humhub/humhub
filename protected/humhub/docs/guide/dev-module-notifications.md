Notifications
=============

Notifications are used to inform one or a small set of users about something.


Basic steps to create a notifications
-------------------------------------

* Create a base folder /mymodule/notifications/
* Create a class ``MyEventNotification`` (Needs to be unique!)

        class MyEventNotification extends Notification {

            // Path to Web View of this Notification
            public $webView = "mymodule.views.notifications.myEvent";

            // Path to Mail Template for this notification
            public $mailView = "application.modules_core.mymodule.views.notifications.myEvent_mail";

            // Implement this method to handle clicks on the notification
            // Note: This is not used when your target_object is of type SIActiveRecordContent
            //public function redirectToTarget() {;}

        }

* Create views ``myEvent_mail.php`` and ``myEvent.php`` in /mymodule/notifications/ (See examples in /modules_core/notifications/views/notifications/spaceInvite[_mail].php)
* Add notification class path to your autostart.php

        'import' => array(
           [...]
           'application.modules.mymodule.notifications.*',
        ),


Fire a notification
-------------------

Example of creating a notification

        $notification = new Notification();
        $notification->class = "MyEventNotification";

        // Which user receives this notification
        $notification->user_id = $userId;

        // Inside a space? (Optional)
        $notification->space_id = $spaceId;

        // Which object throws this notification?
        $notification->source_object_model = 'MyContent';
        $notification->source_object_id = $myContent->id;

        // Which object is the target of the notification (after click on it)
        $notification->target_object_model = $like->object_model;
        $notification->target_object_id = $like->object_id;

        $notification->save();


ToDos / Notes
-------------
* Make Notifications more module friendly (no more need to import notification class path)
* Better Source Object / Target Object Handling
* Cleaner Templates

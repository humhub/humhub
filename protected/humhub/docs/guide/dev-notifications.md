Notifications
=============

Notifications are derived from [[humhub\modules\notification\components\BaseNotification]] and are used to inform one or a given set of users about a specific event.

![Notification Class Diagram](images/notificationClassDiag.jpg)

### Create Notifications

Notifications should reside in the `notifications` directory of a module. (e.g. `/modules/examples/notifications/`)

Example notification: 

```php
<?php

namespace johndoe\example\notifications;

use humhub\modules\notification\components\BaseNotification;

/**
 * Notifies a user about something happend
 */
class SomethingHappend extends BaseNotification
{
	// Module Id (required)
	public $moduleId = "example";

    public $viewName = "somethingHappend";
}
?>
```

By default, the view of a notification should be located inside a subfolder `notifications/views`. (e.g. `/modules/examples/notifications/views/`)

Example view file _somethingHappend.php_:

```php
<?php

use yii\helpers\Html;

echo Yii::t('LikeModule.views_notifications_newLike', "%someUser% did something cool.", array(
    '%someUser%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
));
?>
```

> Info: If you require a different notification view for mails. You have to add a view file to a subfolder `notifications/views/mail`.  

### Send Notifications

```php
$notification = new \johndoe\example\notifications\SomethingHappend();

// Link to the object which fired the notification (optional)
$notification->source = $this;

// The user which triggered the notification (optional)
$notification->originator = $this->user;

// Send it to a set of users
$notification->sendBulk(User::find()->where([...]));

// or: a single user
$notification->send($user);
```
> Info: The `send` and `sendBulk` will create and persist an own [[humhub\modules\notification\models\Notification]] for each user.

### Delete Notifications

By default notifications will automatically be deleted after a given period of time or if the originator(user) object is removed.

Example for manual notification deletion:

```php
$notification = new johndoe\example\notifications\SomethingHappend();
$notification->source = $this;
$notification->delete(User::findOne(['id' => $userId]));
```

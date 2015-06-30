Notifications
=============

Notifications are used to inform one or a set of users about something.


## Steps to create own notifications

### Create 

Create a folder ** notifications ** in your module and a new class ** SomethingHappend ** 

```php
<?php

namespace app\modules\example\notifications;

use humhub\core\notification\components\BaseNotification;

/**
 * Notifies a user about something happend
 */
class SomethingHappend extends BaseNotification
{

    public $viewName = "somethingHappend";

}

?>

```

By default notification views should be located inside a subfolder named ** views ** where your notification class is located. (e.g. /modules/examples/notifications/views/)

Example view file ** somethingHappend.php **:

```php
<?php

use yii\helpers\Html;

echo Yii::t('LikeModule.views_notifications_newLike', "%someUser% did something cool.", array(
    '%someUser%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
));
?>


```

If you require a diffrent view in mails. You can create a subfolder inside the subfolder called ** mail ** in your views directory.  


### Send it 

```php
$notification = new \app\modules\example\notifications\SomethingHappend();

// Link to the object which fired the notification (optional)
$notification->source = $this;

// The user which triggered the notification (optional)
$notification->originator = $this->user;

// Send it to a set of users
$notification->sendBulk(User::find()->where([...]));

// or: a single user
$notification->send($user);

```

### Delete

By default notifications will automatically deleted after a given period of time or if the source / user object not longer exists.

Example for manual notification deletion:

```php
$notification = new \app\modules\example\notifications\SomethingHappend();
$notification->source = $this;
$notification->delete(User::findOne(['id' => $userId]));
```

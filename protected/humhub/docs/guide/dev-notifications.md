Notifications
=============

Notifications are used to inform one or a given set of users about a specific event in your network as the liking of a post or mentioning of a user over multiple channels (e.g. web and mail). 

Custom notification classes are derived from [[humhub\modules\notification\components\BaseNotification]].
A [[humhub\modules\notification\components\BaseNotification|BaseNotification]] usually is assigned with an
`$originator` user instance and a `$source` instance, which connects the Notification with a Content or any other kind of [[yii\db\ActiveRecord]].

A Notification can be sent to a user by calling the `send()` or `sendBulk()` function. This will persist an [[humhub\modules\notification\models\Notification]] instance for each user send out a notification to all allowed NotificationTargets.

> Note: Unlike Activities which are targeted for multiple users e.g. all Users of a Space, a Notification Model instance is always related to a single user.

Examples for core notifications are:

 - [[humhub\modules\like\notifications\NewLike]]: is sent if an user likes a post or comment.
 - [[humhub\modules\user\notifications\Followed]]: is sent if an user follows another user.
 - [[humhub\modules\user\notifications\Mentioned]]: is sent if an user is mentioned within an post or comment.
 - [[humhub\modules\content\notifications\ContentCreated]]: is sent when content (e.g. a post) was created.

## Custom Notifications

#### Notification Class

Custom Notifications are derived from [[humhub\modules\notification\components\BaseNotification|BaseNotification]] and should reside in the `notifications` subfolder of your module's root directory.

The notification class at least has to overwrite the `$moduleId` variable with the id of your module and the.

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

    // Viewname (required)
    public $viewName = "somethingHappend";
}
```

#### Notification View

By default, the view of a notification should be located inside the subfolder `notifications/views`.
The view of the example above should therefore be located in `/modules/examples/notifications/views/somethingHappened.php`.

```php
<?php

use yii\helpers\Html;

echo Yii::t('SomethingHappend.views_notifications_somethingHappened', "%someUser% did something cool.", [
    '%someUser%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
]);
```

> Info: If you require a different notification view for mails, you have to add an extra view file to a subfolder `notifications/views/mail`. 

## Send Notifications

After an event was triggered, you'll have to instantiate your custom [[humhub\modules\notification\components\BaseNotification|BaseNotification]] and call its
`send` or `sendBulk` function which will instantiate and persist a [[humhub\modules\notification\models\Notification]] instance for each user you want to notify.

A notification can optionally be assigned with a `$source` model instance (e.g. a post or comment related to the notification) which has to be derived from [[yii\db\ActiveRecord]].

```php
// Sending to a single user
\johndoe\example\notifications\SomethingHappend::instance()->from($user)->about($source)->send($targetUser);

// Sending to multiple users
\johndoe\example\notifications\SomethingHappend::instance()->from($user)->about($source)->sendBulk($users);
```

> Info: The `send` and `sendBulk` will create and persist a [[humhub\modules\notification\models\Notification]] instance for each user.

> Tip: Notifications are often created and sent within the `afterSave` hook of the related `source` instance. This should be prefered over the instantiation within a controller.

> Note: Notifications are only sent to a specific NotificationTarget depending on the user's account settings.
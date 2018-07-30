Notifications
=============

Notifications are used to inform one or a given set of users about a specific event in your network as the liking of a post or mentioning of a user over multiple channels (e.g. web and mail). 

Custom notification classes are derived from [[humhub\modules\notification\components\BaseNotification]].
A [[humhub\modules\notification\components\BaseNotification|BaseNotification]] usually is assigned with an
`$originator` user instance and a `$source` instance, which connects the Notification with a Content or any other kind of [[yii\db\ActiveRecord]].

A Notification can be sent to a user by calling the `send()` or `sendBulk()` function. This will persist an [[humhub\modules\notification\models\Notification]] instance for each user and send out a notification to all allowed `NotificationTargets`.

Since **HumHub v1.3** Notifications are sent by means of a [queued job](../admin/asynchronous-tasks.md).

Examples for core notifications are:

 - [[humhub\modules\like\notifications\NewLike]]: is sent if an user likes a post or comment.
 - [[humhub\modules\user\notifications\Followed]]: is sent if an user follows another user.
 - [[humhub\modules\user\notifications\Mentioned]]: is sent if an user is mentioned within an post or comment.
 - [[humhub\modules\content\notifications\ContentCreated]]: is sent when content (e.g. a post) was created.

> Note: Unlike [Activities](activities.md) which are targeted for multiple users e.g. all Users of a Space, a Notification is always targeted to a single user.

## Custom Notifications

### Notification Class

Custom Notifications are derived from [[humhub\modules\notification\components\BaseNotification|BaseNotification]] and should reside in the `notifications` directory of your module.

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

By default, the view of a notification should be located inside `notifications/views`.
The view of the example above should therefore be located in `mymodule/notifications/views/somethingHappened.php`.

```php
<?php

use yii\helpers\Html;

echo Yii::t('SomethingHappend.views_notifications_somethingHappened', "%someUser% did something cool.", [
    '%someUser%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
]);
```

> Info: If you require a different notification view for mails, you have to add an extra view file to `notifications/views/mail`. 

## Send Notifications

After an event was triggered, you'll have to instantiate your custom `BaseNotification` and call its
`send()` or `sendBulk()` function.

A notification can optionally be assigned with a `$source` model instance (e.g. a post or comment related to the notification) which has to be derived from [[yii\db\ActiveRecord]].

```php
// Sending to a single user
SomethingHappend::instance()->from($user)->about($source)->send($targetUser);

// Sending to multiple users
SomethingHappend::instance()->from($user)->about($source)->sendBulk($users);
```

Since HumHub v1.3 you have to overwrite `BaseNotification::requireOriginator` or `BaseNotification::requireSource` in case your notification does not require an
`originator` or `source`, otherwise they won't be sent out.

> Info: The `send` and `sendBulk` will create and persist a [[humhub\modules\notification\models\Notification]] instance for each user.

> Tip: Notifications are often created and sent within the `afterSave` hook of the related `source` instance. This should be prefered over the instantiation within a controller.

> Note: Notifications are only sent to a specific NotificationTarget depending on the user's account settings.

### Clear Notifications

Sometimes you want to remove or replace notifications of a specific type.
You can delete notifications similary to its creation syntax:

```php
// Will remove all SomethingHappened notification with the given source for the given $user
SomethingHappend::instance()->about($this)->delete($user);
```
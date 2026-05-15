# Notifications

Notifications inform one or more specific users about an event — a like, a mention, a new comment — and can dispatch through multiple channels (web, mail, mobile push). Compare with [activities](concept-activities.md), which are container-bound and not targeted at a specific user.

A `BaseNotification` typically carries an `$originator` (the user who caused it) and a `$source` (the record the notification is about). Since HumHub 1.3, notifications are dispatched via the [queue](https://docs.humhub.org/docs/admin/asynchronous-tasks), so `send()` returns quickly and the actual fan-out happens out-of-band.

Examples of core notifications:

- `humhub\modules\like\notifications\NewLike` — a user liked a post or comment
- `humhub\modules\user\notifications\Followed` — a user started following another user
- `humhub\modules\user\notifications\Mentioned` — a user was mentioned in a post or comment
- `humhub\modules\content\notifications\ContentCreated` — new content was created

## Implementing a notification

### 1. The notification class

Place it under your module's `notifications/` directory. The class must set `$moduleId` and override `html()`:

```php
namespace johndoe\example\notifications;

use humhub\modules\notification\components\BaseNotification;
use yii\helpers\Html;

class SomethingHappened extends BaseNotification
{
    public $moduleId = 'example';
    public $viewName = 'somethingHappened';

    public function html()
    {
        return Yii::t('ExampleModule.notifications', '{user} did something cool.', [
            '{user}' => '<strong>' . Html::encode($this->originator->displayName) . '</strong>',
        ]);
    }
}
```

### 2. View files (optional)

The default view sits at `notifications/views/<viewName>.php`. Mail uses a separate file at `notifications/views/mail/<viewName>.php` when present, falling back to the default otherwise.

### 3. Sending

```php
// Single target
SomethingHappened::instance()
    ->from($originator)
    ->about($source)
    ->send($targetUser);

// Bulk
SomethingHappened::instance()
    ->from($originator)
    ->about($source)
    ->sendBulk($users);          // array or iterable of User
```

`send()` and `sendBulk()` persist one `humhub\modules\notification\models\Notification` row per recipient and enqueue delivery to every enabled `NotificationTarget`.

If your notification does **not** need an originator or source, override `requireOriginator` / `requireSource` on the class — otherwise sends will silently no-op:

```php
public $requireOriginator = false;
public $requireSource = false;
```

The conventional place to fire a notification is the `afterSave()` hook of the source record. That way it triggers exactly once per state change and the source is guaranteed to exist when the queued job runs.

### Notification targets

Each user's account settings decide which targets receive a given notification category — *Web*, *E-mail*, *Mobile* via the [Firebase module](https://marketplace.humhub.com/module/fcm-push), and so on. The notification framework respects these settings; you don't need to check them yourself.

## Clearing / replacing notifications

When the underlying event is undone (a like is removed, a mention is edited away), delete the matching notification:

```php
SomethingHappened::instance()
    ->about($source)
    ->delete($user);
```

The same matching rule applies as for `send()` — the originator, source and target must agree.

# Users

The user system covers identity, profiles, authentication and user provisioning. Authentication and provisioning are documented separately:

- [Authentication](user-auth.md) — login flow, AuthClient families
- [UserSource](user-source.md) — who owns a user, attribute sync, lifecycle

This page covers the day-to-day model: how to look up the current user, the user/profile relation, and how to participate in user deletion.

## The user component

`Yii::$app->user` is an instance of `humhub\modules\user\components\User`. It provides:

```php
// Is the current request authenticated?
if (Yii::$app->user->isGuest) {
    // ...
}

// Is guest browsing of the platform enabled at all?
use humhub\modules\user\helpers\AuthHelper;
if (AuthHelper::isGuestAccessEnabled()) {
    // ...
}

// Is the current user a system admin?
if (Yii::$app->user->isAdmin()) {
    // ...
}

// Check a global / group permission — see concept-permissions.md
$canDoFoo = Yii::$app->user->getPermissionManager()->can(FooPermission::class);
```

For global / group-scoped permissions, see [permissions → group permissions](concept-permissions.md#group-permissions).

## User identity and profile

`Yii::$app->user->identity` returns the current `humhub\modules\user\models\User` — or `null` for guests. Always null-check when guest access is possible:

```php
$user = Yii::$app->user->identity;
if ($user !== null) {
    $profile = $user->profile;   // humhub\modules\user\models\Profile
}
```

The `User` row carries account-level fields (`username`, `email`, `status`, `last_login`, …); the `Profile` row carries display fields (`firstname`, `lastname`, `birthday`, custom profile fields).

## Participating in user deletion

A module that stores user-related data must clean up on deletion. There are two flavours: soft and hard.

### Soft delete

Soft delete keeps the user's content (posts, comments, …) and removes only personal information and the account itself. Listen for `User::EVENT_BEFORE_SOFT_DELETE` and remove what *your* module considers personal — e.g. task assignments, presence rows, draft attachments.

```php
// config.php
use humhub\modules\user\models\User;
use johndoe\example\Events;

return [
    'events' => [
        [
            'class' => User::class,
            'event' => User::EVENT_BEFORE_SOFT_DELETE,
            'callback' => [Events::class, 'onUserSoftDelete'],
        ],
    ],
];
```

```php
// Events.php
use humhub\modules\user\events\UserEvent;

public static function onUserSoftDelete(UserEvent $event)
{
    $user = $event->user;
    MyParticipations::deleteAll(['user_id' => $user->id]);
}
```

### Hard delete

Hard delete wipes everything: account, profile, content, comments, files, notifications, activities. The core handles most of it automatically — content owned by the user is removed via cascading deletes on `Content`. Override only when you store data that isn't reachable through `Content`:

```php
// config.php
'events' => [
    [
        'class' => User::class,
        'event' => User::EVENT_BEFORE_DELETE,
        'callback' => [Events::class, 'onUserDelete'],
    ],
],
```

```php
// Events.php
public static function onUserDelete($event)
{
    $user = $event->sender;
    MyRecord::deleteAll(['user_id' => $user->id]);
}
```

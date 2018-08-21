Users
=====

User Component
---------------------

The [[\humhub\modules\user\component\User]] component can be accessed by `Yii::$app->user` and beside others provides the following
features:

- Access the [user identity](#user-identity) of the currently logged in user:
- Check for guest user or guest mode activation:

```php
if(Yii::$app->user->isGuest) {
    //...
}

if(Yii::$app->user->isGuestAccessEnabled()) {
    //...
}
```
- Check global [global permissions](permissions.md#group-permissions)
- Check for system admin:

```php
Yii::$app->user->isAdmin()
```

User Identity
---------------------

The user identity of the current logged in user can be accessed by `Yii::$app->user->identity` and in case for non guest
users will return a [[\humhub\modules\user\models\User]] instance.

The user profile can be accessed by `Yii::$app->user->identity->profile` see [[\humhub\modules\user\models\Profile]]

> Note: `Yii::$app->user->identity` may returns `null` in case of guest users. Keep this in mind for guest accessible parts
of your code.

Deleting Users 
---------------------

Users can either be deleted with all their contributions (hard delete) or without, means only their personal/profile data will be deleted (soft delete)

### Soft delete

A common use cases for the soft delete option is:

- Delete participation statuses  (e.g. task assignments)
- Delete personal information and images 

You can manage the soft delete option by intercepting the event [[\humhub\modules\user\models\User::EVENT_BEFORE_SOFT_DELETE]].
 
Example 'config.php':

```php
<?php
  use humhub\modules\user\models\User;
  // ...    
  return [
    // ...    
    'events' => [
        [User::class, User::EVENT_BEFORE_SOFT_DELETE, [Events::class, 'onUserSoftDelete']],
    // ...    
    ],
    // ...    
];
?>
```

Example callback in your modules **Events** class:

```php
public static function onUserSoftDelete(UserEvent $event)
  {
      $user = $event->user;
      MyParticipations::deleteAll(['user_id' => $user->id]); 
  }
```


### Hard delete

The hard delete option will wipe all data in relation with the deleted user.
HumHub objects created by the user like comments, files, posts, notification or activities will automatically be removed with the user profile.

Example 'config.php':

```php
<?php
  use humhub\modules\user\models\User;
  // ...    
  return [
    // ...    
    'events' => [
        [User::class, User::EVENT_BEFORE_DELETE, [Events::class, 'onUserDelete']],
    // ...    
    ],
    // ...    
];
?>
```

Example callback in your modules **Events** class:

```php
public static function onUserDelete(Event $event)
  {
      $user = $event->sender;
      MyRecord::deleteAll(['user_id' => $user->id]); 
  }
```
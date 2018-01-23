Users
=====

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
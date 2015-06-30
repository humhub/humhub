Notifications
=============

Notifications are used to inform one or a set of users about something.




## Steps to create own notifications

### Create 

Create a new class 'SomethingHappend' there

```php
TBD example class
```

By default notifications should be located inside a sub folder named ** view ** where your notification class is located.  e.g. /modules/examples/notifications/views/

```php
TBD
```

If you require diffrent views web & mail. You can create a subfolder inside the view folder called ** mail **.
Locate a mail version of the view there. 


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

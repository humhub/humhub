# Event Handler

One commonly used technique for manipulating existing processes or adding features in a custom module is the implementation of [event handlers](https://www.yiiframework.com/doc/guide/2.0/en/concept-events).

The most common core events used to manipulate existing features are described in the following guide.

Please refer to the [Yii Events Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-events) for general information about the usage of events. Also check the [Events Concept ](concept-events.md) section for information about implementing own events.

## Event handler configuration

Event handlers in HumHub modules are registered within the `events` section of the [config.php](modules#configphp) file. It is recommended to implement the handler functions within a dedicated `Events.php` file in the module root. Some modules may use the `Module.php` class for event handler implementations, this should only be considered for simple and few event handlers in order to keep a clean module structure.

**Example handler configuration**

```php
// config.php
use my\example\Events;
use my\example\modles\Example;

return [
    //...
    'events' => [
        [
            'class' => Example::class, 
            'event' => Example::EVENT_SOME_EVENT,  
            'callback' => [Events::class, 'onSomeEvent']
        ],
        //...
    ]
]
```

| Key        | Description                                                                    |    
|------------|--------------------------------------------------------------------------------|
| `class`    | The namespaced class string of the class responsible for triggering the event. | 
| `event`    | The event name, usually available as class const                               |
| `callback` | Event handler callback class and function name                                 |

When listening to a non core class event, use an actual string definition instead of an import with usage of `::class` to prevent errors when the required module is not enabled.

**Example handler implementation**

```php
// example/Events.php
public static function onSomeEvent($event)
{
    $exampleModel = $event->sender;
    //...
}
```

## Event classes

Depending on the triggered event, there are different types of events provided as event handler argument. Some of the most common event classes are listed in the following table.

| Key                                                                                     | Description                                                          |    
|-----------------------------------------------------------------------------------------|----------------------------------------------------------------------|
| [yii\base\Event](https://www.yiiframework.com/doc/api/2.0/yii-base-event)               | Base event class of Yii                                              | 
| [yii\base\ModelEvent](https://www.yiiframework.com/doc/api/2.0/yii-base-modelevent)     | Supports additional `$isValid` flag                                  |
| [yii\db\AfterSaveEvent](https://www.yiiframework.com/doc/api/2.0/yii-db-aftersaveevent) | Event used for some [ActiveRecord Events](#activerecord-crud-events) |
| [yii\base\ActionEvent](https://www.yiiframework.com/doc/api/2.0/yii-base-actionevent)   | [Controller/Action](#controller-events) related events               |
| [yii\base\WidgetEvent](https://www.yiiframework.com/doc/api/2.0/yii-base-widgetevent)   | [Widget](#widget-events) related events                              |
| [yii\web\UserEvent](https://www.yiiframework.com/doc/api/2.0/yii-web-userevent)         | User identity related events                                         |
| `humhub\components\Event`                                                               | HumHub base event with additional `$result` property                 |
| `humhub\modules\user\events\UserEvent`                                                  | Used for user related events with additional `$user` property        |
| `humhub\modules\user\events\FollowEvent`                                                | Events related to [user following](#user-follow-events)              |
| `humhub\modules\friendship\FriendshipEvent`                                             | Events related to [user friendship](#user-friendship-events)         |
| `humhub\modules\space\MemberEvent`                                                      | Events related to [space memberships](#space-membership)             |
| `humhub\components\ModuleEvent`                                                         | Module related event with `getModule()` function                     |
| `humhub\events\ActiveQueryEvent`                                                        | ActiveQuery related events with additional `$query` property         |
| `custom`                                                                                | Custom modules may implement own event types                         |

## Application Events

[Application](https://www.yiiframework.com/doc/api/2.0/yii-base-application) events can be used to globally intercept requests and actions.

[yii\base\Application](https://www.yiiframework.com/doc/api/2.0/yii-base-application) provides the following events:

| Event                                                                                                                          | Class                  | Description                                                                                   |    
|--------------------------------------------------------------------------------------------------------------------------------|------------------------|-----------------------------------------------------------------------------------------------|
| [Application::EVENT_BEFORE_ACTION](https://www.yiiframework.com/doc/api/2.0/yii-base-module#EVENT_BEFORE_ACTION-detail)        | `yii\base\ActionEvent` | Raised before executing a controller action                                                   |
| [Application::EVENT_AFTER_ACTION](https://www.yiiframework.com/doc/api/2.0/yii-base-module#EVENT_AFTER_ACTION-detail)          | `yii\base\ActionEvent` | Raised  after executing a controller action                                                   | 
| [Application::EVENT_BEFORE_REQUEST](https://www.yiiframework.com/doc/api/2.0/yii-base-application#EVENT_BEFORE_REQUEST-detail) | ` yii\base\Event`      | Raised before the application starts to handle a request                                      |
| [Application::EVENT_AFTER_REQUEST](https://www.yiiframework.com/doc/api/2.0/yii-base-application#EVENT_AFTER_REQUEST-detail)   | ` yii\base\Event`      | Raised after the application successfully handles a request (before the response is sent out) |

`humhub\components\console\Application` additionally triggers the following event

| Event                        | Class                  | Description                   |    
|------------------------------|------------------------|-------------------------------|
| `Application::EVENT_ON_INIT` | `yii\base\ActionEvent` | Raised at the end of `init()` |

## Module Events

### ModuleManager

`humhub\components\ModuleManager` events can be used to listen for [enable](module-development.md#enabled-a-module) and [disable](module-development.md#disable-module)
events of modules. This can be useful in order to listen and react to lifecycle events of dependent modules.

| Event                                        | Class                           | Description                        |    
|----------------------------------------------|---------------------------------|------------------------------------|
| `ModuleManager::EVENT_BEFORE_MODULE_ENABLE`  | `humhub\components\ModuleEvent` | Raised before a module is enabled  | 
| `ModuleManager::EVENT_AFTER_MODULE_ENABLE`   | `humhub\components\ModuleEvent` | Raised after a module is enabled   | 
| `ModuleManager::EVENT_BEFORE_MODULE_DISABLE` | `humhub\components\ModuleEvent` | Raised before a module is disabled | 
| `ModuleManager::EVENT_AFTER_MODULE_DISABLE`  | `humhub\components\ModuleEvent` | Raised after a module is disabled  | 
| `ModuleManager::EVENT_AFTER_FILTER_MODULES`  | `humhub\components\ModuleEvent` | Raised after filter modules        | 

The `humhub\components\ModuleEvent` class provides the following additional properties and functions:

- `$moduleId`: The moduleId of the module -`getModule()`: Can be used to return a module instance

In the following example we use the `ModuleManager::EVENT_AFTER_MODULE_ENABLE` to set a module setting of another module right after the module is enabled.

```php
// config.php
use humhub\components\ModuleManager;

return [
    //..
    'events' => [
        [
            'class' => ModuleManager::class, 
            'event' => ModuleManager::EVENT_BEFORE_MODULE_ENABLE, 
            'callback' => [Event::class, 'onAfterModuleEnabled']
        ]
    ]
]
```

```php
// Events.php
public static function onAfterModuleEnabled(ModuleEvent $event)
{
    if($event->moduleId === 'specialModle') {
        $event->getModule()->settings->set('someSetting', 'someSpecificValue');
    }

}
```

### OnlineModuleManager

`humhub\modules\marketplace\components\OnlineModuleManager` provides events triggered before and after a module was updated by the marketplace.

| Event                                      | Class                           | Description                       |    
|--------------------------------------------|---------------------------------|-----------------------------------|
| `OnlineModuleManager::EVENT_BEFORE_UPDATE` | `humhub\components\ModuleEvent` | Raised before a module is updated | 
| `OnlineModuleManager::EVENT_AFTER_UPDATE`  | `humhub\components\ModuleEvent` | Raised after a module is updated  | 

## Controller Events

[Controller](https://www.yiiframework.com/doc/api/2.0/yii-base-controller#events) events can be used to intercept requests of web or console controller actions and manipulate the controller result.

| Event                                                                                                                      | Class                  | Description                                       |    
|----------------------------------------------------------------------------------------------------------------------------|------------------------|---------------------------------------------------|
| [Controller::EVENT_AFTER_ACTION](https://www.yiiframework.com/doc/api/2.0/yii-base-controller#EVENT_AFTER_ACTION-detail)   | `yii\base\ActionEvent` | Raised right after executing a controller action  | 
| [Controller::EVENT_BEFORE_ACTION](https://www.yiiframework.com/doc/api/2.0/yii-base-controller#EVENT_BEFORE_ACTION-detail) | `yii\base\ActionEvent` | Raised right before executing a controller action |

In the following example we listen to all web controller requests and redirect to a custom action in case some special condition is not met.

```php
// config.php
use my\example\Events;
use humhub\components\Controller;

return [
    //...
    'events' => [
        ['class' => Controller::class, 'event' => Controller::EVENT_BEFORE_ACTION, 'callback' => [Events::class, 'onBeforeControllerAction']]
    ]
]
```

```php
// Events.php
public static function onBeforeControllerAction(ActionEvent $event)
{
    if(!static::checkSomeSpecialCondition($event)) {
         // Do not continue running the action.
         $event->isValid = false;
         // Manipulate action result
         $event->result = Yii::$app->response->redirect(['/example/special-condition/index']);
    }
}
```

See [Yii Controller Events](https://www.yiiframework.com/doc/api/2.0/yii-base-controller#events) for more information about the usage of Controller events.

### CronController Events

Events of the `humhub\commands\CronController` can be handled in order to implement scheduled tasks.

| Event                                 | Class            | Description   |    
|---------------------------------------|------------------|---------------|
| `CronController::EVENT_ON_HOURLY_RUN` | `yii\base\Event` | Raised hourly | 
| `CronController::EVENT_ON_DAILY_RUN`  | `yii\base\Event` | Raised daily  |

Use the `CronController::EVENT_BEFORE_ACTION` in case you want to implement a custom scheduling interval.

## Response Events

[Response](https://www.yiiframework.com/doc/api/2.0/yii-web-response) events can be used to manipulate the server response.

| Event                                                                                                                 | Class            | Description                                          |    
|-----------------------------------------------------------------------------------------------------------------------|------------------|------------------------------------------------------|
| [Response::EVENT_AFTER_PREPARE](https://www.yiiframework.com/doc/api/2.0/yii-web-response#EVENT_AFTER_PREPARE-detail) | `yii\base\Event` | Raised right after `prepare()` is called in `send()` | 
| [Response::EVENT_AFTER_SEND](https://www.yiiframework.com/doc/api/2.0/yii-web-response#EVENT_AFTER_SEND-detail)       | `yii\base\Event` | Raised at the end of `send()`                        |
| [Response::EVENT_BEFORE_SEND](https://www.yiiframework.com/doc/api/2.0/yii-web-response#EVENT_BEFORE_SEND-detail)     | `yii\base\Event` | at the beginning of `send()`                         |

See [Yii Response Events](https://www.yiiframework.com/doc/api/2.0/yii-web-response#events) for more information.

## Model validation

The base [Model](https://www.yiiframework.com/doc/api/2.0/yii-base-model) class supports events useful to intercepting the
**validation** of a model.

| Event                                                                                                                | Class                 | Description                             |    
|----------------------------------------------------------------------------------------------------------------------|-----------------------|-----------------------------------------|
| [Model::EVENT_BEFORE_VALIDATE](https://www.yiiframework.com/doc/api/2.0/yii-base-model#EVENT_BEFORE_VALIDATE-detail) | `yii\base\Event`      | Raised at the beginning of `validate()` | 
| [Model::EVENT_AFTER_VALIDATE](https://www.yiiframework.com/doc/api/2.0/yii-base-model#EVENT_AFTER_VALIDATE-detail)   | `yii\base\ModelEvent` | Raised at the end of `validate()`       |

In the following example we implement a handler for the `EVENT_BEFORE_VALIDATE` of the `humhub\modules\user\models\Invite` model in order to intercept the registration process.

```php
// config.php
use humhub\modules\user\models\Invite;

return [
    //..
    'events' => [
        [
            'class' => Invite::class, 
            'event' => Invite::EVENT_BEFORE_VALIDATE, 
            'callback' => [Events::class, 'onInviteBeforeValidate']
        ]
    ]
]
```

```php
// Events.php
public static function onInviteBeforeValidate($event)
{
    $registrationForm = $event->sender;
    $user = $registrationForm->models['User'];

    if (self::autoEnable($user->email) {
        $user->status = User::STATUS_ENABLED;
        $registrationForm->enableUserApproval = false;
    } else if (!self::isAllowed($user->email)) {
        $user->status = User::STATUS_DISABLED;
        $user->addError('email', Yii::t('EnterpriseModule.emailwhitelist', 'The given email address is not allowed for registration!'));
    }
}
```

## ActiveRecord (CRUD) Events

The [ActiveRecord](https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord) class supports additional events for intercepting
[CRUD](https://en.wikipedia.org/wiki/Create,_read,_update_and_delete) related events. Those events are useful for manipulating model properties or synchronizing the creation/deletion of custom models with models of other modules.

| Event                                                                                                                             | Class                   | Description                                                        |    
|-----------------------------------------------------------------------------------------------------------------------------------|-------------------------|--------------------------------------------------------------------|
| [ActiveRecord::EVENT_AFTER_DELETE](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_AFTER_DELETE-detail)    | `yii\base\Event`        | Raised  after a record is deleted                                  | 
| [ActiveRecord::EVENT_AFTER_FIND](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_AFTER_FIND-detail)        | `yii\base\ModelEvent`   | Raised after the record is created and populated with query result |
| [ActiveRecord::EVENT_AFTER_INSERT](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_AFTER_INSERT-detail)    | `yii\db\AfterSaveEvent` | Raised after a record is inserted                                  |
| [ActiveRecord::EVENT_AFTER_REFRESH](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_AFTER_REFRESH-detail)  | `yii\base\Event`        | Raised after a record is refreshed                                 |
| [ActiveRecord::EVENT_AFTER_UPDATE](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_AFTER_UPDATE-detail)    | `yii\db\AfterSaveEvent` | Raised after a record is updated                                   |
| [ActiveRecord::EVENT_BEFORE_INSERT](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_BEFORE_INSERT-detaill) | `yii\base\ModelEvent`   | Raised before inserting a record                                   |
| [ActiveRecord::EVENT_BEFORE_UPDATE](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_BEFORE_UPDATE-detail)  | `yii\base\ModelEvent`   | Raised before updating a record                                    |
| [ActiveRecord::EVENT_INIT](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_INIT-detail)                    | `yii\base\Event`        | Raised  when the record is initialized via `init()`                |

**Example use cases**:

- Listen for  `EVENT_AFTER_INSERT` and `EVENT_BEFORE_DELETE` events of a specific record type in order to synchronize the creation or deletion of custom model relations
- Listen for `EVENT_BEFORE_SAVE` to manipulate the properties of a model prior to persisting it to the database
- Listen for `EVENT_AFTER_FIND` to manipulate model properties right after model queries.

See [Yii ActiveRecord Events](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#events) for more information about the usage of ActiveRecord events.

## User Events

### User Model

The `humhub\modules\user\models\User` model provides the following additional events:

| Event                            | Class                                  | Description                                          |    
|----------------------------------|----------------------------------------|------------------------------------------------------|
| `User::EVENT_CHECK_VISIBILITY`   | `humhub\modules\user\events\UserEvent` | Can be used to add conditions to `User::isVisible()` | 
| `User::EVENT_BEFORE_SOFT_DELETE` | `humhub\modules\user\events\UserEvent` | Raised after a soft deletion of a user model         | 

The following example adds an user visibility condition:

```php
// config.php
use my\example\Events;
use humhub\modules\user\model\Use;

return [
    //...
    'events' => [
        [
            'class' => Use::class, 
            'event' => Use::EVENT_CHECK_VISIBILITY, 
            'callback' => [Events::class, 'onUserIsVisible']
        ]
    ]
]
```

```php
// Events.php
public static function onUserIsVisible(UserEvent $event)
{
    if($event->user->username === 'secretUser') {
        $event->result['isVisible'] = false;
    }
}
```

### ActiveQueryUser Events

The `humhub\modules\user\components\ActiveQueryUser` events may be used to add conditions
`ActiveQueryUser::visible()` (in addition to `Use::EVENT_CHECK_VISIBILITY`) and `ActiveQueryUser::active()`. This will manipulate the results of queries like:

```php
User::find()->active()->visible()->all();
```

| Event                                     | Class                            | Description                          |    
|-------------------------------------------|----------------------------------|--------------------------------------|
| `ActiveQueryUser::EVENT_CHECK_VISIBILITY` | `humhub\events\ActiveQueryEvent` | Raised within `visible()` user query | 
| `ActiveQueryUser::EVENT_CHECK_ACTIVE`     | `humhub\events\ActiveQueryEvent` | Raised within `active()` user query  |

In the following example we add an additional check to `ActiveQueryUser::visible()` by checking a custom `user` table field. In this example the `user.is_visible_in_crm` was added to the user table by our custom module.

```php
// config.php
use my\example\Events;
use humhub\modules\user\components\ActiveQueryUser;

return [
    //...
    'events' => [
        [
            'class' => ActiveQueryUser::class, 
            'event' => ActiveQueryUser::EVENT_CHECK_VISIBILITY, 
            'callback' => [Events::class, 'onUserQueryVisible']
        ]
    ]
]
```

```php
// Events.php
public static function onUserQueryVisible(ActiveQueryEvent $event)
{
     $event->query->andWhere(['user.is_visible_in_crm' => 1]);
}
```

### Registration Events

The `humhub\modules\user\models\forms\Registration` class provides events raised within the registration process:

| Event                                    | Class               | Description                          |    
|------------------------------------------|---------------------|--------------------------------------|
| `Registration::EVENT_AFTER_REGISTRATION` | `yii\web\UserEvent` | Raised after successful registration |

The following example shows how to synchronize the registration process with an external crm service.

```php
// config.php
use my\example\Events;
use humhub\modules\user\models\forms\Registration;

return [
    //...
    'events' => [
        [
            'class' => Registration::class, 
            'event' => Registration::EVENT_AFTER_REGISTRATION, 
            'callback' => [Events::class, 'onUserRegistration']
        ]
    ]
]
```

```php
// Events.php
public static function onUserRegistration(UserEvent $event)
{
     try {
        $service = new CrmService();
        $result = $service->createOrUpdateSubscriber($evt->identity);
        // Check for errors or log result etc...
        static::handleCrmServiceResult($result);
    } catch(\Exception $e) {
        Yii::error($e);
    }
}
```

### User Follow Events

User follow events are triggered once a user follows or unfollows a content, space or another user by the `humhub\modules\user\models\Follow`
class.

| Event                             | Class                                    | Description                                       |    
|-----------------------------------|------------------------------------------|---------------------------------------------------|
| `Follow::EVENT_FOLLOWING_CREATED` | `humhub\modules\user\events\FollowEvent` | Raised when a user follows a content/space/user   | 
| `Follow::EVENT_FOLLOWING_REMOVED` | `humhub\modules\user\events\FollowEvent` | Raised when a user unfollows a content/space/user |

The following example sends out a custom `FollowAchievementNotification` notification once the user reaches a certain amount of followers. The notification is removed in case the follower decreases to lower thant 5.

```php
// config.php
use my\example\Events;
use humhub\modules\user\models\Follow;

return [
    //...
    'events' => [
        [
            'class' => Follow::class, 
            'event' => Follow::EVENT_FOLLOWING_CREATED, 
            'callback' => [Events::class, 'onUserFollow']
        ],
        [
            'class' => Follow::class, 
            'event' => Follow::EVENT_FOLLOWING_REMOVED, 
            'callback' => [Events::class, 'onUserUnfollow']
        ],
    ]
]
```

```php
// Events.php
public static function onUserFollow(FollowEvent $event)
{
    if($event->target instanceof User) {
        if($event->target->getFollowerCount() === 5) {
            FollowAchievementNotification::instance()->send($event->target);
        }
    }
}

public static function onUserUnfollow(FollowEvent $event)
{
   if($event->target instanceof User) {
       if($event->target->getFollowerCount() < 5) {
           FollowAchievementNotification::instance()->delete($event->target);
       }
    }
}
```

### User Friendship Events

Similar to the follow events, the friendship module triggers an events once a user friendship relation is created or removed. The events are triggered by the `humhub\modules\friendship\models\Friendship` model.

| Event                                  | Class                                       | Description                             |    
|----------------------------------------|---------------------------------------------|-----------------------------------------|
| `Friendship::EVENT_FRIENDSHIP_CREATED` | `humhub\modules\friendship\FriendshipEvent` | Raised when a user makes a new friend   | 
| `Friendship::EVENT_FRIENDSHIP_REMOVED` | `humhub\modules\friendship\FriendshipEvent` | Raised when a user removes a friendship |

The `humhub\modules\friendship\FriendshipEvent` class provides the following additional properties:

- `$user1`: The user initiating the friendship -`$user2`: The user who received the friendship request

### Space Membership

Space membership events are triggered by the `humhub\modules\space\models\Membership` model and can be used to trace the creation or removal of space membership relations.

| Event                              | Class                              | Description                                      |    
|------------------------------------|------------------------------------|--------------------------------------------------|
| `Membership::EVENT_MEMBER_ADDED`   | `humhub\modules\space\MemberEvent` | Raised when a user joins a space or was added    |
| `Membership::EVENT_MEMBER_REMOVED` | `humhub\modules\space\MemberEvent` | Raised when a user leaves a space or was removed |

The `humhub\modules\space\models\Membership` class provides the following additional properties:

- `$space`: The `humhub\modules\space\models\Space` model of the space -`$user`: The respective user model of the membership relation

## Widget Events

[Widget](https://www.yiiframework.com/doc/api/2.0/yii-base-widget) events can be used to extend the output of a widget or even overwrite widget classes.

| Event                                                                                                        | Class                           | Description                                        |    
|--------------------------------------------------------------------------------------------------------------|---------------------------------|----------------------------------------------------|
| [Widget::EVENT_INIT](https://www.yiiframework.com/doc/api/2.0/yii-base-widget#EVENT_INIT-detail)             | `yii\base\Event`                | Raised when the widget is initialized via `init()` | 
| [Widget::EVENT_BEFORE_RUN](https://www.yiiframework.com/doc/api/2.0/yii-base-widget#EVENT_BEFORE_RUN-detail) | `yii\base\WidgetEvent`          | Raised right before executing a widget             |
| [Widget::EVENT_AFTER_RUN](https://www.yiiframework.com/doc/api/2.0/yii-base-widget#EVENT_AFTER_RUN-detail)   | `yii\base\WidgetEvent`          | Raised right after executing a widget              |
| `humhub\components\Widget::EVENT_CREATE`                                                                     | `humhub\libs\WidgetCreateEvent` | Raised before `Yii::createObject()`                |

### Extend widget output

The `Widget::EVENT_AFTER_RUN` can be used to extend the output of a widget, as in the following example:

```php
// config.php
use my\example\Events;
use humhub\modules\content\widgets\richtext\ProsemirrorRichTextEditor;

return [
    //...
    'events' => [
        [
            'class' => ProsemirrorRichTextEditor::class, 
            'event' => ProsemirrorRichTextEditor::EVENT_AFTER_RUN, 
            'callback' => [Events::class, 'onRichTextEditorFieldCreate']
        ]
    ]
]
```

```php
// Events.php
public static function onRichTextEditorFieldCreate(WidgetEvent $event)
{
    $event->result .= '<div>Powered by example Module</div>';
}
```

### Prevent widget from rendering

The `Widget::EVENT_BEFORE_RUN` can be used prevent widgets from rendering:

```php
// config.php
use my\example\Events;
use humhub\components\Widget;

return [
    //...
    'events' => [
        [
            'class' => 'some\module\widget\SpecialWidget', 
            'event' => Widget::EVENT_BEFORE_RUN, 
            'callback' => [Events::class, 'onSpecialWidgetBeforeRun']]
    ]
]
```

```php
// Events.php
public static function onSpecialWidgetBeforeRun(WidgetEvent $event)
{
    if(static::someSpecialCondition($event)) {
        $event->isValid = false;
    }
}
```

### Overwrite a widget class

The `humhub\components\Widget::EVENT_CREATE` can be used to overwrite the widget class.

```php
// config.php
use my\example\Events;
use humhub\components\Widget;

return [
    //...
    'events' => [
        [
            'class' => 'some\module\widget\SpecialWidget', 
            'event' => Widget::EVENT_CREATE, 
            'callback' => [Events::class, 'onSpecialWidgetCreate']]
    ]
]
```

```php
// Events.php
public static function onSpecialWidgetCreate(WidgetCreateEvent $event)
{
    $event->config['class'] = MyCustomSpecialWidget::class;
}
```

Since the widget is created by a call to `Yii::createObject()` the new widget class has to be compatible with the overwritten one, which means it has to support all possible properties of the original widget class. This could be achieved by extending the original widget.

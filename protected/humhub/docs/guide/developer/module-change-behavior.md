Change the behavior of core features
=================

This guide describes possible ways for modules to change the behavior of core features.

## Events

One commonly used way of intercepting core processes or adding new features within modules are `event handlers`.
Please refer to the [Yii Events Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-events) for general information
about the usage of events. And also check the [Module Events](modules.md#module-events) section.

The following list shows an overview of most of the events available in the HumHub core:

- Models:
  - `Model::EVENT_BEFORE_VALIDATE`
  - `Model::EVENT_AFTER_VALIDATE`
- ActiveRecord:
  - `ActiveRecord::EVENT_INIT`
  - `ActiveRecord::EVENT_AFTER_FIND`
  - `ActiveRecord::EVENT_BEFORE_INSERT`
  - `ActiveRecord::EVENT_AFTER_INSERT`
  - `ActiveRecord::EVENT_BEFORE_UPDATE`
  - `ActiveRecord::EVENT_AFTER_UPDATE`
  - `ActiveRecord::EVENT_BEFORE_DELETE`
  - `ActiveRecord::EVENT_AFTER_DELETE`
  - `ActiveRecord::EVENT_AFTER_REFRESH`
- Controller:
  - `Controller::EVENT_INIT`
  - `Controller::EVENT_BEFORE_ACTION`
  - `Controller::EVENT_AFTER_ACTION`
- Application
  - `Application::EVENT_ON_INIT`
- CronController:
  - `CronController::EVENT_ON_HOURLY_RUN`
  - `CronController::EVENT_ON_DALY_RUN`
- Integrity:
  - `IntegrityController::EVENT_ON_RUN`
- Response:
  - `Response::EVENT_BEFORE_SEND`
  - `Response::EVENT_AFTER_SEND`
  - `Response::EVENT_AFTER_PREPARE`
- ModuleManager:
  - `ModuleManager::EVENT_BEFORE_MODULE_ENABLED`
  - `ModuleManager::EVENT_AFTER_MODULE_ENABLED`
  - `ModuleManager::EVENT_BEFORE_MODULE_DISABLED`
  - `ModuleManager::EVENT_AFTER_MODULE_DISABLED`
- Widget:
  - `Widget::EVENT_CREATE`
  - `Widget::EVENT_INIT`
  - `Widget::EVENT_BEFORE_RUN`
  - `Widget::EVENT_AFTER_RUN`
- AbstractRichText:
  - `AbstractRichText::EVENT_POST_PROCESS`
  - `AbstractRichText::EVENT_BEFORE_OUTPUT`
- BaseStack:
  - `BaseStack::EVENT_INIT`
  - `BaseStack::EVENT_RUN`
- BaseMenu:
  - `BaseMenu::EVENT_INIT`
  - `BaseMenu::EVENT_RUN`
- User:
  - `User::EVENT_CHECK_VISIBILITY`
  - `User::EVENT_BEFORE_SOFT_DELETE`
- Registration:
  - `Registration::EVENT_AFTER_REGISTRATION`
- ActiveQueryUser:
  - `ActiveQueryUser::EVENT_CHECK_VISIBILITY`
  - `ActiveQueryUser::EVENT_CHECK_ACTIVE`
- Follow:
  - `Follow::EVENT_FOLLOWING_CREATED`
  - `Follow::EVENT_FOLLOWING_REMOVED`
- Friendship:
    - `Friendship::EVENT_FRIENDSHIP_CREATED`
    - `Friendship::EVENT_FRIENDSHIP_REMOVED`
- Search:
  - `Search::EVENT_SEARCH_ATTRIBUTES`
  - `Search::EVENT_ON_REBUILD`
- FileHandlerCollection:
  - `FileHandlerCollection::EVENT_INIT`
- humhub\modules\installer\Module:
  - `humhub\modules\installer\Module::EVENT_INIT_CONFIG_STEPS`
- humhub\modules\installer\controllers\ConfigController:
  - `humhub\modules\installer\controllers\ConfigController::EVENT_INSTALL_SAMPLE_DATA`
- Search:
  - `Search::EVENT_SEARCH_ATTRIBUTES`
  - `Search::EVENT_ON_REBUILD`
- Search:
  - `Search::EVENT_SEARCH_ATTRIBUTES`
  - `Search::EVENT_ON_REBUILD`
- Searchable:
  - `Searchable::EVENT_SEARCH_ADD`
- Membership:
  - `Membership::EVENT_MEMBER_REMOVED`
  - `Membership::EVENT_MEMBER_ADDED`
- Stream:
  - `Stream::EVENT_BEFORE_RUN`
  - `Stream::EVENT_AFTER_RUN`
- StreamQuery:
  - `StreamQuery::EVENT_BEFORE_FILTER`
- BaseClient:
  - `BaseClient::EVENT_UPDATE_USER`
  - `BaseClient::EVENT_CREATE_USER`
- humhub\modules\user\authclient\Collection 
  - `Collection::EVENT_AFTER_CLIENTS_SET`

## Widgets

Widget events are often used to extend view components as menus and forms. Here are some use-cases for widget events:

### Extend menus

Widgets based on [[humhub\widgets\BaseMenu]] and [[humhub\widgets\BaseStack]] can be intercepted and extended by means of the [[humhub\widgets\BaseMenu::EVENT_INIT]] event.
Please see the [Sidebars and Snippets](snippet.md#event-handlers) section for useful examples of such event handlers.

### Overwrite widget classes

In order to completely replace a widget implementation you can use the [[humhub\components\Widget::EVENT_CREATE]] as follows:

**config.php:**

```php
'events' => [
    ['class' => SomeWidgetIWantToOverwrite::class, 'event' => Widget::EVENT_CREATE, 'callback' => [Events::class, 'onCreateSomeWidget']],
]
```

**Events.php:**

```php
public static function onCreateSomeWidget($event)
{
    $event->config['class'] = MyOverwriteWidget::class;
}
```

> Note: Your replacement widget have to support the same fields as the original widget.

### Append widget content

In some cases you may want to append or otherwise manipulate the output of a widget. For this use-case you can listen to the
[[yii\base\Widget::EVENT_AFTER_RUN]] event. The following example appends the output of `MyWidgetToAppend` to the result of
`SomeWidgetIWantToExtend`:

**config.php:**

```php
'events' => [
    ['class' => SomeWidgetIWantToExtend::class, 'event' => Widget::EVENT_AFTER_RUN, 'callback' => [Events::class, 'onSomeWidgetRun']],
]
```

**Events.php:**

```php
public static function onSomeWidgetRun($event)
{
    $event->result .= MyWidgetToAppend::widget();
}
```

## Module Interfaces

Some Modules as for example the [Calendar Module](https://github.com/humhub/humhub-modules-calendar/blob/master/docs/interface.md) provide interfaces in order to
facilitate its features.

## Javascript

See [Overwrite Module Behavior](javascript-index.md#overwrite-module-behaviour) in the javascript guide.

## Embeded Theme

Your custom module can be bundled with custom themes, please see the [Theme - Module Integration](../theme/module.md) section for more information.



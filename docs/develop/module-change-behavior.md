# Change existing features

This guide describes some techniques available for custom modules to change or extend the behavior of existing features.

The following list shows an overview of most of the events available in the HumHub core:

- Integrity:
  - `IntegrityController::EVENT_ON_RUN`
   
- AbstractRichText:
  - `AbstractRichText::EVENT_POST_PROCESS`
  - `AbstractRichText::EVENT_BEFORE_OUTPUT`
- BaseStack:
  - `BaseStack::EVENT_INIT`
  - `BaseStack::EVENT_RUN`
- BaseMenu:
  - `BaseMenu::EVENT_INIT`
  - `BaseMenu::EVENT_RUN`

- Searchable:
  - `Searchable::EVENT_SEARCH_ADD`
- Search:
  - `Search::EVENT_SEARCH_ATTRIBUTES`
  - `Search::EVENT_ON_REBUILD`
  
- FileHandlerCollection:
  - `FileHandlerCollection::EVENT_INIT`
- humhub\modules\installer\Module:
  - `humhub\modules\installer\Module::EVENT_INIT_CONFIG_STEPS` // How to even use this?
- humhub\modules\installer\controllers\ConfigController:
  - `humhub\modules\installer\controllers\ConfigController::EVENT_INSTALL_SAMPLE_DATA`

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

Widgets based on `humhub\widgets\BaseMenu` and `humhub\widgets\BaseStack` can be intercepted and extended by means of the `humhub\widgets\BaseMenu::EVENT_INIT]] event.
Please see the [Sidebars and Snippets](ui-snippets.md#event-handlers) section for useful examples of such event handlers.

### Overwrite widget classes

In order to completely replace a widget implementation you can use the `humhub\components\Widget::EVENT_CREATE` as follows:

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
[yii\base\Widget::EVENT_AFTER_RUN](https://www.yiiframework.com/doc/api/2.0/yii-base-widget#EVENT_AFTER_RUN-detail) event. The following example appends the output of `MyWidgetToAppend` to the result of
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

See [Overwrite Module Behavior](ui-js-overview.md#overwrite-module-behaviour) in the javascript guide.

## Embeded Theme

Your custom module can be bundled with custom themes, please see the [Theme - Module Integration](https://docs.humhub.org/docs/theme/module) section for more information.


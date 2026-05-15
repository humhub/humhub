# Changing existing features

Techniques for changing or extending what the core (or another module) does, without forking it.

The core fires hundreds of events; the canonical catalogue is [module-event-handler.md](module-event-handler.md). This page focuses on the *patterns* you'll most often want.

## Extend an existing menu

Menu and stack widgets derived from `humhub\modules\ui\menu\widgets\Menu` (formerly `BaseMenu`) and `humhub\widgets\BaseStack` can be intercepted via their `EVENT_INIT` event. See [sidebars and snippets → event handlers](ui-snippets.md#event-handlers) for examples and the canonical pattern.

## Replace a widget

To swap out an existing widget with your own implementation, listen for `humhub\components\Widget::EVENT_CREATE` and rewrite `$event->config['class']`:

```php
// config.php
'events' => [
    [
        'class' => SomeWidgetIWantToOverwrite::class,
        'event' => Widget::EVENT_CREATE,
        'callback' => [Events::class, 'onCreateSomeWidget'],
    ],
]
```

```php
// Events.php
public static function onCreateSomeWidget($event)
{
    $event->config['class'] = MyOverwriteWidget::class;
}
```

The replacement widget must accept the same public properties as the original — callers pass arbitrary configuration via constructor args, and your replacement has to handle whatever they send.

## Append content to a widget's output

To add to the rendered output without replacing the widget, listen for `yii\base\Widget::EVENT_AFTER_RUN` and append to `$event->result`:

```php
// config.php
'events' => [
    [
        'class' => SomeWidgetIWantToExtend::class,
        'event' => Widget::EVENT_AFTER_RUN,
        'callback' => [Events::class, 'onSomeWidgetRun'],
    ],
]
```

```php
// Events.php
public static function onSomeWidgetRun($event)
{
    $event->result .= MyWidgetToAppend::widget();
}
```

## Module-provided interfaces

Some modules publish dedicated PHP interfaces or hook collections rather than expecting you to listen for events. Examples:

- [Calendar module — `CalendarItemTypesEvent`](https://github.com/humhub/humhub-modules-calendar/blob/master/docs/interface.md)

When such an integration point exists, prefer it over an ad-hoc event handler.

## JavaScript overrides

See [overwriting module behaviour](ui-js-overview.md#overwrite-module-behaviour) in the JavaScript guide.

## Embedded themes

Custom modules can bundle their own themes. See the [theme — module integration](https://docs.humhub.org/docs/theme/module) guide.

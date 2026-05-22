# Events

Events are the primary extension point in HumHub. To change or augment core behaviour, listen for class-level events fired by widgets, ActiveRecords, controllers, the application — and hundreds of others. See [`module-event-handler.md`](module-event-handler.md) for the full catalogue and event payload reference.

The general concept follows Yii — read the [Yii events guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-events) for the underlying model.

This page covers two things modules need:

1. How to **listen** for someone else's event (via `config.php`)
2. How to **emit** your own events so other modules can listen

## Registering an event listener

Listeners are declared in your module's [`config.php`](module-development.md#configphp). The recommended pattern is a dedicated `Events.php` class with static handlers — using the `Module` class works for one or two handlers but gets cluttered fast.

```php
// example/config.php
use humhub\widgets\TopMenu;
use johndoe\example\Events;

return [
    'id' => 'example',
    // ...
    'events' => [
        [
            'class' => TopMenu::class,
            'event' => TopMenu::EVENT_INIT,
            'callback' => [Events::class, 'onTopMenuInit'],
        ],
    ],
];
```

The handler receives the event object that the emitter created:

```php
// example/Events.php
namespace johndoe\example;

class Events
{
    public static function onTopMenuInit($event)
    {
        $event->sender->addItem([
            'label' => 'Example',
            'icon' => '<i class="fa fa-tachometer"></i>',
            'url' => '#',
            'sortOrder' => 200,
        ]);
    }
}
```

When listening for events from a non-core class, **pass the class name as a string** rather than via `::class` — that way the config doesn't blow up if the target module is not installed:

```php
'events' => [
    ['class' => 'some\optional\module\SomeClass', 'event' => 'EVENT_FOO', 'callback' => [Events::class, 'onFoo']],
],
```

## Defining your own events

To expose an extension point in your module, fire an event from the relevant code path. Choose the event base class that matches the data your listeners need:

- `yii\base\Event` — base, generic
- `yii\base\ModelEvent` — adds an `$isValid` flag listeners can flip to veto an action
- `humhub\components\Event` — adds a `$result` property that listeners can populate

```php
use humhub\components\Event;

class ExampleManager
{
    public const EVENT_BEFORE_PROCESS = 'beforeProcess';

    public function process(Example $example): void
    {
        $event = new Event(['sender' => $this, 'data' => $example]);
        $this->trigger(self::EVENT_BEFORE_PROCESS, $event);

        // ...
    }
}
```

Document the event constant, the event class type, and the payload — that's the public API listeners will couple to.

Events
======

In order to extend or alter the behavior of some features, your module can listen to class level events as for example:

 - **Widget** events
 - **ActiveRecord** validation,save or delete events
 - **Application** events
 - **Controller** events
 
Events are configured within your modules `config.php` file as described in the previous section. Module event handler should ideally reside in an
extra `Events` class, especially if you plan multiple event handlers. In simpler cases events handlers may be implemented directly within the `Module` class
itself.

See [change the default behaviour](module-change-behavior.md) for additional event use-cases.

[http://www.yiiframework.com/doc-2.0/guide-concept-events.html](http://www.yiiframework.com/doc-2.0/guide-concept-events.html)

### Catching an Event

Example event section of the **config.php** file:

```php
'events' => [
    [
		'class' => \humhub\widgets\TopMenu::class, 
		'event' => \humhub\widgets\TopMenu::EVENT_INIT, 
		'callback' => [Module::class, 'onTopMenuInit'],
    ], 
 ]
```

### Processing 

Example of event callback:

```php
public static function onTopMenuInit($event)
{
    $event->sender->addItem([
        'label' => "Example",
        'icon' => '<i class="fa fa-tachometer"></i>',
        'url' => '#',
        'sortOrder' => 200,
    ]);
}
```

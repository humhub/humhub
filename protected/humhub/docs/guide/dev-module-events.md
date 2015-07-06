Events
======

TBD

[http://www.yiiframework.com/doc-2.0/guide-concept-events.html](http://www.yiiframework.com/doc-2.0/guide-concept-events.html)



### Catching an Event

Example event section of the config.php file:

```php
// ...
'events' => [
    [
		'class' => \humhub\widgets\TopMenu::className(), 
		'event' => \humhub\widgets\TopMenu::EVENT_INIT, 
		'callback' => [Module::className(), 'onTopMenuInit'],
    ], 
	// ...
 ]
// ...
```

### Processing 

Example of event callback:

```php
public static function onTopMenuInit($event)
{
    $event->sender->addItem(array(
        'label' => "Example",
        'icon' => '<i class="fa fa-tachometer"></i>',
        'url' => '#',
        'sortOrder' => 200,
    ));
}
```

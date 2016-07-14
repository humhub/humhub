Widget Stack
============

- Base class: [[humhub\widgets\BaseStack]] 
- Use Cases: Sidebars, ...

TBD


## Usage 

Example of stack used as sidebar.

```php
<?php
echo \humhub\core\space\widgets\Sidebar::widget(['widgets' => [
        [\humhub\core\activity\widgets\Stream::className(), ['streamAction' => '/space/space/stream', 'contentContainer' => $space], ['sortOrder' => 10]],
        [\humhub\core\space\widgets\Members::className(), ['space' => $space], ['sortOrder' => 20]]
]]);
?>
```

## Events

### Example

__config.php__

```php
    //...
    'events' => array(
        // Wait for TopMenu Initalization Event
        array('class' => 'DashboardSidebarWidget', 'event' => 'onInit', 'callback' => array('ExampleModule', 'onDashboardSidebarInit')),
    ),
    //...
```


__Events.php__

```php
    public static function onDashboardSidebarInit($event) {
        $event->sender->addWidget('application.modules.example.widgets.MyCoolWidget', array(), array('sortOrder' => 1));
    }
```
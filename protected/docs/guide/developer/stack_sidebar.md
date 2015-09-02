StackWidget / Sidebars
======================

All sidebar widget classes inherits the base class ``StackWidget`` which allows modules
to inject additional own widgets to it.

## Example of Sidebars

* DashboardSidebarWidget (Module: dashboard) 
* SpaceSidebarWidget (Module: space) 
* ProfileSidebarWidget (Module: user)

## Example

__autostart.php__
```php
    //...
    'events' => array(
        // Wait for TopMenu Initalization Event
        array('class' => 'DashboardSidebarWidget', 'event' => 'onInit', 'callback' => array('ExampleModule', 'onDashboardSidebarInit')),
    ),
    //...
```

__ExampleModule.php__
```php
    public static function onDashboardSidebarInit($event) {
        $event->sender->addWidget('application.modules.example.widgets.MyCoolWidget', array(), array('sortOrder' => 1));
    }
'
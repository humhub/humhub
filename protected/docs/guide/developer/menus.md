Menus
=====

All navigations widget classes inherits the base class ``MenuWidget`` which allows modules
to inject own items into navigation menu.

## Events of MenuWidget

* onInit    - at initialization of a menu
* onRun     - before the menu is rendered

## List of Menus

* TopMenuWidget - Top Navigation
* ProfileMenuWidget - User Profile Menu (User Module)
* AccountMenuWidget - User Account Menu (User Module)
* SpaceMenuWidget - Space Menu (Space Module)
* SpaceAdminMenuWidget - Space Admin Menu (Space Module)
* AdminMenuWidget - Admininistration Menu (Admin Module)

## Example

__autostart.php__
```php
    //...
    'events' => array(
        // Wait for TopMenu Initalization Event
        array('class' => 'TopMenuWidget', 'event' => 'onInit', 'callback' => array('ExampleModule', 'onTopMenuInit')),
    ),
    //...
```

__ExampleModule.php__
```php
    public static function onTopMenuInit($event) {
        $event->sender->addItem(array(
            'label' => 'My new top menu item',
            'url' => 'http://www.google.de',
        ));
    }
'
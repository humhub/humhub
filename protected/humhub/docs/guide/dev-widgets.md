Widgets
============

Widgets are used to provide reusable view parts by means of a view class. Please refer to the [Yii-Guide](http://www.yiiframework.com/doc-2.0/guide-structure-widgets.html)
for more information about widgets.

## Widget Stacks

HumHub uses Widget-Stacks to assamble multiple entries of a base widget as a naviagation or list.
Stacked widget are derived from [[humhub\widgets\BaseStack]] and will fire an `onInit` and `onRun` event by default,
which can be subscribed by other modules to inject widget items. This mechanism can be used for example for sidebars.

Example of stack used as sidebar:

```php
<?php
// Render the sidebar with two default item
echo \humhub\core\space\widgets\Sidebar::widget(['widgets' => [
        [\humhub\core\activity\widgets\Stream::className(), ['streamAction' => '/space/space/stream', 'contentContainer' => $space], ['sortOrder' => 10]],
        [\humhub\core\space\widgets\Members::className(), ['space' => $space], ['sortOrder' => 20]]
]]);
?>
```

__config.php__

```php
    // Subscribe to the onInit event of the sidebar
    'events' => array(
        // Wait for TopMenu Initalization Event
        array('class' => 'DashboardSidebarWidget', 'event' => 'onInit', 'callback' => array('ExampleModule', 'onDashboardSidebarInit')),
    ),
    //...
```

__Events.php__

```php
    // This handler function will inject a custom widget to the stack
    public static function onDashboardSidebarInit($event) {
        $event->sender->addWidget('application.modules.example.widgets.MyCoolWidget', array(), array('sortOrder' => 1));
    }
```

## Menus

All navigations widget classes inherit from the class [[humhub\widgets\BaseMenu]], which allows modules
to inject own items into navigation menu.

Example: 

__config.php__ - Catching Event

```php
use humhub\widgets\TopMenu;

return [
    //...
    'events' => [
        ['class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => ['humhub\modules\calendar\Events', 'onTopMenuInit']],
    ],
]
```


__Events.php__ - Handling the Event

```php
//...
public static function onTopMenuInit($event)
{
    if (Yii::$app->user->isGuest) {
        return;
    }

    $user = Yii::$app->user->getIdentity();
    if ($user->isModuleEnabled('calendar')) {
        $event->sender->addItem([
            'label' => Yii::t('CalendarModule.base', 'Calendar'),
            'url' => Url::to(['/calendar/global/index']),
            'icon' => '<i class="fa fa-calendar"></i>',
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'calendar' && Yii::$app->controller->id == 'global'),
            'sortOrder' => 300,
        ]);
    }
}
//...
```

Menus
=====

All navigations widget classes inherits the base class [[humhub\widgets\BaseMenu]] which allows modules
to inject own items into navigation menu.

## Example

__config.php__ - Catching Event

```php
//...
use humhub\widgets\TopMenu;
//...
'events' => array(
    array('class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => array('humhub\modules\calendar\Events', 'onTopMenuInit')),
),
//...
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
        $event->sender->addItem(array(
            'label' => Yii::t('CalendarModule.base', 'Calendar'),
            'url' => Url::to(['/calendar/global/index']),
            'icon' => '<i class="fa fa-calendar"></i>',
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'calendar' && Yii::$app->controller->id == 'global'),
            'sortOrder' => 300,
        ));
    }
}
//...
```

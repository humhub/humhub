Menus and Navigations
=====================

All menus and navigation widgets are derived from the widget class [[humhub\modules\ui\menu\widgets\Menu]].

Additionally, there are following sub base classes with predefined templates available:

- [[humhub\modules\ui\menu\widgets\LeftNavigation]]
- [[humhub\modules\ui\menu\widgets\TabMenu]]
- [[humhub\modules\ui\menu\widgets\SubTabMenu]]
- [[humhub\modules\ui\menu\widgets\DropDownMenu]]


Based on these base classes, following menu implementations are most frequently used:

- TopMenu (Main navigation with entries like Dashboard/Directory) -  [[humhub\widgets\TopMenu]]
- FooterMenu - [[humhub\widgets\FooterMenu]]
- AdminMenu - Administrative Section -  [[humhub\modules\admin\widgets\AdminMenu]]
- AccountMenu - [[humhub\modules\user\widgets\AccountTopMenu]]


Menu entries are represented by the class [[humhub\modules\ui\menu\MenuEntry]]. 
Instances of this class can be added via the menu class.

See the [[humhub\modules\ui\menu\MenuEntry]] class for a full list of properties and options.


Events
------

The menu allow you to manipulate menu entries at certain execution points using events.

You can use all Yii2 widget events to interact with the menu class.


Example of the modules **config.php**:

```php
return [
    'id' => 'example',
    'class' => Module::class,
    'events' => [
        ['class' => TopMenu::class, 'event' => TopMenu::EVENT_INIT, 'callback' => ['\humhub\modules\example\Events', 'onTopMenuInit']],
    ]
];
```


Example of a callback:


```php

namespace humhub\modules\example;

use humhub\modules\dashboard\widgets\ShareWidget;
use humhub\modules\ui\widgets\Icon;
use humhub\modules\ui\menu\MenuEntry;
use humhub\widgets\TopMenu;

use Yii;
use yii\base\Event;
use yii\helpers\Url;

/**
 * Description of Events
 *
 * @author luke
 */
class Events
{

    /**
     * TopMenu init event callback
     *
     * @see TopMenu
     * @param Event $event
     */
    public static function onTopMenuInit($event)
    {
        /** @var TopMenu $topMenu */
        $topMenu = $event->sender;

        $entry = new MenuEntry();

        $entry->setId('dashboard');
        $entry->setLabel(Yii::t('DashboardModule.base', 'Dashboard'));
        $entry->setUrl(['/dashboard/dashboard']);
        $entry->setIcon(new Icon(['name' => 'tachometer']));
        $entry->setSortOrder(100);
        $entry->setIsActive((Yii::$app->controller->module && Yii::$app->controller->module->id === 'dashboard'));

        $topMenu->addEntry($entry);
    }

}


``` 

 
ToDos
-----

- Add UnitTesting
- Allow submenus
- Separator Entry support
- Location for Footer/TopMenu sources? Move into UI module?
- PanelMenu Cleanup

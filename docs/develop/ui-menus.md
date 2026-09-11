# Menus

All menus and navigation widgets are derived from the widget class `humhub\widgets\menu\Menu`.

Additionally, there are following sub base classes with predefined templates available:

- `humhub\widgets\menu\LeftNavigation`
- `humhub\widgets\menu\TabMenu`
- `humhub\widgets\menu\SubTabMenu`
- `humhub\widgets\menu\DropdownMenu`

Based on these base classes, following menu implementations are most frequently used:

| Class                                                | Where it appears                                  |
|------------------------------------------------------|---------------------------------------------------|
| `humhub\widgets\TopMenu`                             | Main navigation (Dashboard, Directory, …)         |
| `humhub\widgets\FooterMenu`                          | Footer                                            |
| `humhub\modules\admin\widgets\AdminMenu`             | Administration section                            |
| `humhub\modules\user\widgets\AccountTopMenu`         | Account dropdown                                  |

Menu entries are represented by the class `humhub\widgets\menu\MenuEntry`. 
Instances of this class can be added via the menu class.

See the `humhub\widgets\menu\MenuEntry` class for a full list of properties and options.

## Events

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
use humhub\widgets\Icon;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\TopMenu;

use Yii;
use yii\base\Event;
use yii\helpers\Url;

class Events
{
    public static function onTopMenuInit($event)
    {
        /** @var TopMenu $topMenu */
        $topMenu = $event->sender;

        $entry = new MenuLink();

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

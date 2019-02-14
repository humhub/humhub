Widgets
============

Widgets are used to provide reusable view parts by means of a view class. Please refer to the [Yii-Guide](http://www.yiiframework.com/doc-2.0/guide-structure-widgets.html)
for more information about widgets.

This guide describes the usage of some HumHubs base widget types.

## JsWidgets

JsWidgets in HumHub are used to connect your Yii Widget with a [javascript widget](javascript-components.md#widgets).
Custom JsWidget are extended from [[humhub\widgets\JsWidget]] and can facilitate the following features:

 - Transfer options from PHP to your javascript widget
 - Manage your widget initialization through the `init` field
 - Reloadable widget
 - Widget event binding

> Info: Refer to the [Javascript Widget](javascript-components.md#widgets) part for more information about writing javascript modules.
 
A very basic JsWidget:

**SimpleWidget.php**

```php
namespace humhub\modules\devtools\widgets;


use Yii;
use humhub\widgets\JsWidget;
use yii\helpers\Url;

class SimpleWidget extends JsWidget implements Reloadable
{
    public $jsWidget = 'example.SimpleWidget';
    
    /**
     * This will automatically initialize the widget
     */
    public $init = true;
    
    /**
     * Used to add HTML attributes to the root of your widget
     */
    public function getAttributes()
    {
        return [
            'class' => 'my-widget'
        ]
    }
    
    /**
     * Used to add data-* options to your widget
     */
    public function getData()
    {
        return [
            'some-url' : Url::to(['/some/route'])
        ]
    }
}
```

**example.SimpleWidget.js**

```javascript

humhub.module('example.SimpleWidget', function(module, require, $) {
     var Widget = require('ui.widget').Widget;
    
     var SimpleWidget = Widget.extend();
    
     SimpleWidget.prototype.init = function() {
        console.log(this.options.someUrl)
     };
     
     module.export = SimpleJsWidget;
});
```

### JsWidget rendering

The example above does not overwrite the [[humhub\widgets\JsWidget::run()]] function and therefore uses the default
rendering of the JsWidget class, which can be used for very simple widgets. The default rendering mechanism uses renders
a simple HTML tag defined by [[humhub\widgets\JsWidget::container]] and [[humhub\widgets\JsWidget::content]] and furthermore
uses the [[humhub\widgets\JsWidget::getOptions()]] function for fetching the HTML attributes and widget data-* options.

In the following example we manipulate the default rendering to render a simple list:

```php
class SimpleWidget extends JsWidget implements Reloadable
{
    public $jsWidget = 'example.SimpleWidget';
    
    public $init = true;
    
    public $id = 'iamUnique'
    
    public $container = 'ul';
    
    public $myOption = 300;
    
    public function run()
    {
      $this->content = Html::tag('li', 'My List Item');
      return parent::render();
    }
    
    public function getAttributes()
    {
        return [
            'class' => 'my-widget-list'
        ]
    }
        
    public function getData()
    {
        return [
            'some-important-option' : $this->myOption
        ]
    }
}
```

This will result in the following output:

```html
<ul id="iamUnique" class="my-widget-list" data-ui-widget="example.SimpleWidget" data-ui-init data-some-important-option="300">
    <li>My List Item</li>
</ul>
```

Fore more complex cases you may want to use a custom view as follows:

```php
public function run()
{
  return $this->render('myWidget', [
    'model' => $this->model,
    'options' => $this->getOptions();
  ])
}
```

**myWidget.php**

```php
<?= Html::beginTag('div', $options) ?>
  // Some complex widget content...
<?= Html::endTag('div') ?>
```

> Note: The `getOptoins()` function assembles all html attributes and data-* options of your widget, which always should be added
to the root node of your widget.

### JsWidget initialization

The initialization is managed by the [[humhub\widgets\JsWidget::init]] field, which either accepts a boolean or array.
Please refer to [Widget Initialization](javascript-components.md#widget-initialization) for the javascript part of your
initialization logic.

When setting your `init` field to true or add a value, your widget will be initialized once detected in the frontend,
otherwise your widget may be initialized by an action trigger or manually within your javascript module.

By providing a non boolean value as `init` value you can add serialized data which will be used as first parameter of 
your `SimpleWidget.prototype.init` function.

### Reloadable JsWidgets

Often you want to reload your widget in order to update parts of your view. This can be achieved by implementing the
[[humhub\widgets\Reloadable]] interface and providing a reload-url in your `getReloadUrl()` as in the following example

```php
class ReloadableWidget extends JsWidget implements Reloadable
{
    //...
    
    public function getReloadUrl()
    {
      return ['/mymodule/widget/reload', 'id' => $this->id];
    }
}
```

```php
class WidgetController extends Controller
{
    public function actionReload($id)
    {
        return JsonResponse::output(ReloadableWidget::widget(['id' => $id]));
    }
}
```

Now you will be able to reload your widget with `myWidget.reload()` or a `reload` button action.

## Widget Stacks

HumHub uses Widget-Stacks to assemble multiple entries of a base widget as a naviagation or list.
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

# Widgets

Widgets package a reusable view fragment behind a class — see [Yii's widget guide](https://www.yiiframework.com/doc/guide/2.0/en/structure-widgets) for the underlying concept. This page documents the HumHub-specific extensions: `JsWidget` (a widget with a paired JavaScript module) and `BaseStack` (an event-driven container for other widgets).

## JsWidget

`humhub\widgets\JsWidget` connects a PHP widget to a [JavaScript widget](ui-js-components.md#widgets). It handles the wiring you'd otherwise repeat in every module:

- Pass options from PHP into JS via `data-*` attributes
- Auto-initialise the JS side via the `init` field
- Reload itself on demand via the `Reloadable` interface
- Bind events declaratively

### Minimal example

```php
// SimpleWidget.php
namespace johndoe\example\widgets;

use humhub\widgets\JsWidget;
use yii\helpers\Url;

class SimpleWidget extends JsWidget
{
    public $jsWidget = 'example.SimpleWidget';
    public $init = true;          // run the JS init() automatically on detect

    public function getAttributes()
    {
        return [
            'class' => 'my-widget',
        ];
    }

    public function getData()
    {
        return [
            'some-url' => Url::to(['/some/route']),
        ];
    }
}
```

```js
// example.SimpleWidget.js
humhub.module('example.SimpleWidget', function (module, require, $) {
    var Widget = require('ui.widget').Widget;

    var SimpleWidget = Widget.extend();

    SimpleWidget.prototype.init = function () {
        console.log(this.options.someUrl);
    };

    module.export = SimpleWidget;
});
```

Note how `some-url` on the PHP side becomes `options.someUrl` (camelCase) in JS — `data-*` attributes are converted on read.

### Rendering

The default `JsWidget::run()` emits a single HTML tag — `JsWidget::$container` for the tag name and `JsWidget::$content` for its inner HTML. That's enough for most widgets:

```php
class SimpleWidget extends JsWidget
{
    public $jsWidget = 'example.SimpleWidget';
    public $init = true;
    public $id = 'iamUnique';
    public $container = 'ul';
    public $myOption = 300;

    public function run()
    {
        $this->content = Html::tag('li', 'My List Item');
        return parent::run();
    }

    public function getAttributes()
    {
        return ['class' => 'my-widget-list'];
    }

    public function getData()
    {
        return ['some-important-option' => $this->myOption];
    }
}
```

Output:

```html
<ul id="iamUnique" class="my-widget-list" data-ui-widget="example.SimpleWidget" data-ui-init data-some-important-option="300">
    <li>My List Item</li>
</ul>
```

For more complex output, render a view file and pass the assembled HTML attributes via `$this->getOptions()`:

```php
public function run()
{
    return $this->render('myWidget', [
        'model' => $this->model,
        'options' => $this->getOptions(),
    ]);
}
```

```php
<?= Html::beginTag('div', $options) ?>
    <!-- complex widget content -->
<?= Html::endTag('div') ?>
```

`getOptions()` aggregates HTML attributes *and* `data-*` options, so it must be applied to the root element — that's what the JS side looks for when binding.

### Initialisation

`JsWidget::$init` decides whether the JS init runs automatically once the markup is detected in the DOM:

| Value          | Behaviour                                                                  |
|----------------|----------------------------------------------------------------------------|
| `false` (default) | No auto-init. Trigger via action or manually in JS.                      |
| `true`         | Auto-init. JS `init()` is called with no arguments.                        |
| `array`/`scalar` | Auto-init. The value is serialised and passed as the first arg of `init()`. |

See [JavaScript components → widget initialisation](ui-js-components.md#widget-initialization) for the JS side.

### Reloadable widgets

Implement `humhub\widgets\Reloadable` and supply a `getReloadUrl()` to make a widget reloadable from JS:

```php
use humhub\widgets\Reloadable;

class ReloadableWidget extends JsWidget implements Reloadable
{
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

Reload from JS via `myWidget.reload()` or a `reload` action button.

## Widget stacks

`humhub\widgets\BaseStack` is a container that assembles multiple widgets into a navigation, sidebar or list. Stacks fire `onInit` and `onRun` events so other modules can inject items — this is the canonical pattern for plugging into sidebars and similar surfaces.

```php
echo \humhub\modules\space\widgets\Sidebar::widget([
    'widgets' => [
        [\humhub\modules\stream\widgets\Stream::class, ['streamAction' => '/space/space/stream', 'contentContainer' => $space], ['sortOrder' => 10]],
        [\humhub\modules\space\widgets\Members::class, ['space' => $space], ['sortOrder' => 20]],
    ],
]);
```

Subscribe to the stack's init event to inject your own widget from a module:

```php
// example/config.php
use humhub\modules\space\widgets\Sidebar;
use johndoe\example\Events;

return [
    'events' => [
        [
            'class' => Sidebar::class,
            'event' => Sidebar::EVENT_INIT,
            'callback' => [Events::class, 'onSidebarInit'],
        ],
    ],
];
```

```php
// example/Events.php
public static function onSidebarInit($event)
{
    $event->sender->addWidget(
        \johndoe\example\widgets\MyCoolWidget::class,
        [],
        ['sortOrder' => 1]
    );
}
```

See [sidebars and snippets](ui-snippets.md) for sidebar-specific patterns and [change behavior → extend an existing menu](module-change-behavior.md#extend-an-existing-menu) for menu-style stacks.

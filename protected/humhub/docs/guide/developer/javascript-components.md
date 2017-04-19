Javascript UI Components
=======

UI Components can be used to bind specific parts of your view to a Javascript Widgets defined in your module. This can be achieved by extending the `action.Component` or the more powerful `ui.Widget` class.

## Simple Components


Components consist of a root node, which can be accessed by `this.$` within your component functions as in the following example.


###### View:

```php
<div id="myComponent" data-action-component="example.MyComponent">
    <div class="message"></div>
    <button data-action-click="hello">Say Hi!</button>
</div>
```

###### Module:

```javascript
humhub.module('example.MyComponent', function(module, require, $) {
    var object = require('util').object;
    var Component = require('action').Component;

    var MyComponent = function(node, options) {
        Component.call(this, node, options);
    }

    // Make sure this is called before your function definitions, otherwise they will be lost!
    object.inhertis(MyComponent, Component);

    MyComponent.prototype.hello = function(evt) {
        this.$.find('.message').text('Hi!');
    }

    module.export = MyComponent;
});
```

After clicking the button of the previous example the `action` module will search for the closest surrounding component with existing `hello` action handler and execute it.

#### Get a Component Instance

If you need the instance of your component, for example in another module, you can retrieve it by calling `Component.instance`:

```javascript
humhub.module('example.two', function(module, require, $) {
    var Component = require('action').Component;

    var myOtherAction = function() {
        var myComponent = Component.instance('#myComponent');
    }

    [...]
});
```

> Tip: You can also search for a component by using `Component.closest` or `Component.find`

#### Nested Components

Components can be nested, which can be handy for example if you want to implement a list view with a single root componentent and multiple entries, whereas the root component could define some configurations as urls and texts or some helper actions.

###### View:

```php
<div id="myComponent" data-action-component="example.mylist.List" data-some-setting="1">
    <div data-action-component="example.mylist.ListEntry" data-id="1" >...</div>
    <div data-action-component="example.mylist.ListEntry" data-id="2" >...</div>
</div>
```

###### Module:

```javascript
humhub.module('example.mylist', function(module, require, $) {
    var object = require('util').object;
    var Component = require('action').Component;

    // our parent component
    var List = function(node, options) {
        Component.call(this, node, options);
    }

    object.inhertis(List, Component);

    ListEntry.prototype.listAction = function(evt) {
        /* ... */
    }

    // child component
    var ListEntry = function(node, options) {
        Component.call(this, node, options);
    }

    object.inhertis(ListEntry, Component);

    List.prototype.someAction = function(evt) {

        // we can access the data setting of our parent (if not overwritten by our own root)
        if(this.data('some-setting') {
             /* ... */
        }
        
        // access parent component
        this.parent().listAction();
    }

    module.export({
        List: List,
        ListEntry: ListEntry
    });
});
```
> Note: The `data` function of a component will search for a given data attribute on the components own root node and if not present will search the parent components for the data attribute.

## Widgets

The `humhub.modules.ui.widget.Widget` class extends the `action.Component` class and provides some additional functionality as:

- Advanced event handling
- Eager initialization
- Widget options


#### Widget Initialization

A Widgets `init` function is called once the widget is created. A Widget is created either immediately within the humhub initialization phase in case the widgets root node contains a `data-ui-init` flag or lazily by calling a widget action or initializing the Widget by means of  calling `Widget.instance('#myWidget')`.

> Note: If you load a Widget by an ajax call, make sure to apply the [ui.additions](javascript-uiadditions.md) on your inserted dom node, otherwise the `data-ui-init` behaviour won't be recognized.

###### View:
```php
<div id="myWidget" data-ui-widget="example.MyWidget" data-ui-init="1" style="display:none">
    <!-- ... -->
</div>
```

###### Module:

```javascript
humhub.module('example.MyWidget', function(module, require, $) {
    var object = require('util').object;
    var Widget = require('ui.widget').Widget;

    var MyWidget = function(node, options) {
        Widget.call(this, node, options);
    }

    // Make sure this is called before your function definitions, otherwise they will be lost!
    object.inhertis(MyWidget, Widget);

    MyWidget.prototype.init = function() {
        this.$.fadeIn('fast');
    }

    module.export = MyWidget;
});
```

#### Widget Options

Your Widget options can be set by using `data-*` attributes on your Widgets root node.
The Widgets `getDefaultOptions()` method can be used to define default Widget options.

###### View:
```php
<div id="myWidget" data-ui-widget="example.MyWidget" data-some-setting="0">
    <!-- ... -->
</div>
```

###### Module:

```javascript
humhub.module('example.MyWidget', function(module, require, $) {
    var object = require('util').object;
    var Widget = require('ui.widget').Widget;

    var MyWidget = function(node, options) {
        Widget.call(this, node, options);
    }

    object.inhertis(MyWidget, Widget);
    
    var MyWidget.prototype.getDefaultOptions = function() {
        return {
            someSetting: '1'
        }
    }

    MyWidget.prototype.init = function() {
        if(this.options.someSetting) {
            /* ... */
        } else {
           /* ... */
        }
    }

    module.export = MyWidget;
});
```

> Note: Notice the transformation of `data-some-setting` to the camelcase option name `someSetting`.

#### Widget Events
TBD

#### JsWidget class

In order to implement a Yii widget responsible for rendering your widgets markup, you can extend hte [[humhub\widgets\JSWidget]] class as in the following examples.

##### Default widget rendering:

The following example shows a simple JsWidget implementation without overwriting the widgets `run` method.

```php
class MyWidget extends \humhub\widgets\JsWidget
{
    // Javascript widget namespace
    public $jsWidget = 'example.MyWidget';
    
    // will add the data-ui-init="1"
    public $init = true;

    // defines the root container node name
    public $container = 'div'; 

    // our widget setting
    public $someSetting = '1';
    
	public function getAttributes()
    {
        return [
            'class' => 'myWidget'
        ];
    }    


    public function getData()
    {
        return [
           'some-setting' : $this->someSetting
        ];
    }
}
```

The following `JSWidget` attributes are available:

- `id`: the widget root id, if not provided a generated id will be used by default
- `jsWidget`: defines the Javascript widget namespace
- `init`: will add the data-ui-init flag if set to true
- `visible`: can be set to false in case the root node should be rendered hidden on startup
- `options`: used to overwrite or set the Widgets htmlOptions
- `events`: defines widget action events
- `container`: defines the root node name when using the default rendering mechanism
- `content`: defines the content of the root node when using the default rendering mechanism

Functions:

- `getData()`: returns an array of widget settings which will be transformed into `data-*` attributes.
- `getAttributes()`: returns an array of html attributes/values
- `getOptions()`: merges the given `options` with the result of `getData()` and `getAttributes()` and is used as root node options in most of the cases

> Note: in this case the `container` setting could be omitted, since `div` is the default container name.

The widget could be used within a view as follows:

```php
<?= 
    MyWidget::widget([
        'someSetting' => '0', // overwrite the default setting
        'content' => 'Some content'
    ]);
?>
```

which would render the following output:

```html
<div class="myWidget" data-some-setting="0" data-ui-init="1">Some content</div>
```

##### Custom widget rendering:

For more complex JsWidgets, you can overwrite your widgets `run` method and use the `getOptions` method to merge the widgets `options` with the default options provided by `getData` und `getAttributes` as follows.

```php
class MyWidget extends \humhub\widgets\JsWidget
{
    public $jsWidget = 'example.MyWidget';
    public $someSetting = '0';

    /* ... */

    public function run()
    {
        $this->render('myWidgetView', ['options' => $this->getOptions()]);
    }
    
    public function getAttributes()
    {
        return [
            'class' => 'myWidget'
        ];
    }    


    public function getData()
    {
        return [
           'some-setting' : $this->someSetting
        ];
    }
}
```

**myWidgetView.php**

```php
<?= Html::beginTag('div', $options); ?>
    <!-- Complex widget markup -->
<?= Html:: endTag('div');
```
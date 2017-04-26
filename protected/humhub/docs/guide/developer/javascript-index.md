Javascript Modules
=======

Since version 1.2, HumHub provides a module based Javascript API within the `humhub` namespace.
Instead of embeding inline script blocks into your views, it's highly recommended to store your Javascript code in external script files and ideally use the HumHub module system.

Your module scripts should reside in the `resources/js` directory of your modules root directory.

## Modules System

### Publish a Module Asset

In order to add your script files to your view, you should use an [Asset Bundle](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html) residing within the `assets` directory of your module. 

By setting `public $jsOptions = ['position' => \yii\web\View::POS_END];`, your assets will be appended to the end of the document body. This will assure all core modules are already registered. If you require your script beeing loaded ealrier you can also set `\yii\web\View::POS_BEGIN`, which will add you script at the beginning of the document body.

```php
namespace humhub\modules\example\assets;

use yii\web\AssetBundle;

class ExampleAsset extends AssetBundle
{
    public $jsOptions = ['position' => \yii\web\View::POS_END];
    public $sourcePath = '@example/resources';
    public $js = [
        'js/humhub.example.js'
    ];
}
```

The [Asset Bundle](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html) can be registered to a view by calling 

```php
\humhub\modules\example\assets\ExampleAsset::register($this);
```

Where `$this` is the view instance. More infos about Asset Bundles are available in the [Yii Guide](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html).

> Note: Make sure to add your assets after the core scripts, which are added within the documents head section.

> Note: Yii loads Javascript Files only once per page load, therefore all your script files will only be loaded and executed once. This can lead to unexpected behaviour especially with [Pjax](javascript-client.md#pjax) single page loading enabled.

> Note: If your bundle is registered to a view retrieved by an ajax call, make sure to render your view by using your controllers `$this->renderAjaxContent()` function. In contrast to `renderPartial()`, this function will add all your asset dependencies to your partial content. Don't use the `renderAjaxContent` to include a view into your page outside of an ajax call, this will include some script twice and will lead to unexpected behaviour.

### Register Modules

Modules are registered by calling the `humhub.module()` function as follows

```javascript
humhub.module('example', function(module, require, $) {
   ...
});
```
The previous `example` module will be added to the following namespace `humhub.modules.example`. 

You can also register sub modules as in the following example

```javascript
humhub.module('example.mySubModule', function(module, require, $) {
  ...
});
```

The first argument of the `humhub.module()` function defines the **module id** which also defines the namespace appended to the `humhub.modules`. The second argument is the actual **module function**.

> Note: You should use an unique namespace for your custom modules as `myproject.mymodule`, otherwise it may interfere with other modules.

Your module function is provided with the following arguments:

1. `module` - Your module instance, used for exporting module logic and accessing module specific utilities as `log`, `text`, `config`
2. `require` - Used for injecting other modules.
3. `$` - jQuery instance.

#### Export Module Logic

Module functions and attributes can only be accessed outside of the module if they are exported, either by directly appending them to the `module` instance or by calling `module.export`.

```javascript
humhub.module('example', function(module, require, $) {

    // private function
    var private = function() { /* ... */ }
   
    // direct export of public function
    module.publicFunction = function() {/* ... */}

    // another public function exported later
    var publicTwo = function() { /* ... */}

    // Exports multiple values
    module.export({
        publicTwo: publicTwo,
        publicThree: function() {/** Test function **/}
    });
});
```
In case you only want to **export a single object/function/class** you can use the following syntax:

```javascript
/* ... */
var MyClass = function() {/*...*/};

MyClass.prototype.myFunction = function() {/*..*/}

module.export = MyClass;
/* ... */
```
> Note: When exporting a single object or class, the plain object or function will be added to the given namespace without including the usual module attributes and utilities mentioned later in this guide.

#### Module Initialization

Your module can define its initialization logic by exporting an `init` function.

By default this function is only called once after a full page load or directly after the registration in case the module was loaded per ajax. If your module requires an initialization also after [Pjax](javascript-client.md) page loads, your module has to set the  `initOnPjaxLoad` flag. In this case the `init` function will provide an `isPjax` flag for beeing able to distinguish between full page loads and [Pjax](javascript-client.md) page loads.

```javascript
/* ... */
module.initOnPjaxLoad = true;

var init = function(isPjax) { 
    // Do some global initialization work, which needs to run in any case
    if(isPjax) {
        // Runs only after a pjax page load
    } else {
        // Runs only after fresh page load
    }
}

module.export({
    init: init
});
/* ... */
```
> Tip: You'll need the `initOnPjaxLoad` flag for modules which rely on specific dom elements or specific views.

> Warning: Once registered, your modules `init` function may be called even if you are not currently in your desired modules view. This occures especially if [Pjax](javascript-pjax.md) is enabled and `initOnPjaxLoad` is set to `true`. Therfore, if your modules initialization logic only makes sense in a specific context, make sure you reside in the desired view before running your actual initialization code e.g: `if(!$('#mySpecialViewElement').length) {return;}`.

#### Module Unload

For the purpose of cleaning up module related dom nodes etc. your module can export an `unload` function which is called before each Pjax page load. This function is mainly used to remove obsolete dom nodes outside of the main content area, prevent memory leaks, remove obsolete dom listeners or clear up some module data.  

```javascript
var unload = function($pjax) { 
    $('.moduleResidues').remove();
}

module.export({
    unload: unload
});
```

> Note: Some third party libraries append helper elements to the document body. Make sure to remove such elements in the `unload` function. 

#### Module Dependencies

Other modules can be injected into your module by using the `require` function as follows 

```javascript
var mySubModule = require('example.mySubModule');

//  require by using the full path (full path)
var example = require('humhub.modules.example');

// Also a valid call
require('example').myFunction();

// Calling myFunction outside of a module
humhub.modules.example.myFunction();
```

It's good practice to require all dependent modules at the beginning of your module. When doing so consider the loading order of those modules. Since all core modules are registered in the head section of your document, they are available within the document body.

If your module requires another module, which is not part of the core API, you can ensure the order by means of the `$depends` attribute of your Asset Bundle class.


```php
public $depends = [
    'humhub\modules\anotherModule\assets\AnotherModuleAsset'
];
```

> Note: You can only `depend` Assets with a higher or equal `$jsOption position`.

In case you can't assure the registration order of a required  module, but need to import the module, you can either require the module on demand within your module function or use the `lazy` flag of the require function. 

The call to `require('anotherModule', true)` will return an empty namespace object in case the dependent module has not been registered yet. The actual module logic will be available after the dependent module is registered.

> Warning: When using the `lazy` flag, you can't assure the required module is already initialized within your own modules `init` logic.

```javascript
humhub.module('example', function(module, require, $) {
    // We can't ensure that module2 is registered before our module
    var module2 = require('module2', true); 

    // at this point module2 might be empty
    
    var myFunction = function() {
        // myFunction should only be used outside of the init logic
        module2.executeSomeFunction();
    }

    var init = function() {
        // This is dangerous!!!
        module2.doSomething(); 
    }
});
```

> Info: All core modules are appended to the head section of your document, so there should not be any dependency problem for those modules if you append your assets either at the begin or the end of the document body.

### Module Lifecycle

A module runs through the following lifecycle (by the example of our `example` module):

1. **Full Page load**
2. Calling `humhub.module` - the module is registered but not initialized
3. **Document Ready**
4. `humhub:beforeInitModule`
5. `humhub:modules:example:beforeInit` 
6. Calling the modules `init` function with `isPjax = false`
7. `humhub:modules:example:afterInit` 
8. `humhub:afterInitModule` 
9. `humhub:ready` 
10. **Pjax call**
11. `humhub:modules:client:pjax:beforeSend`
12. Calling the modules `unload` function
13. `humhub:modules:client:pjax:success`
14. Reinitialize all modules with `initOnPjaxLoad=true` by calling `init` with `isPjax = true` 
15. `humhub:ready` 

### Module Configuration

If you need to transfer values as texts, flags or urls from your php backend to your Javascript module, you can use the `module.config` array as follows

```javascript
humhub.module('example', function(module, require, $) {
...

    var myAction = function() {
        if(module.config.showMore) {
            // Do something
        }
    };
});
```

The configuration can be set in your php view as follows

```php
// Single module
$this->registerJsConfig('example', ['showMore' => true]);

// ...or multiple modules
$this->registerJsConfig([
    'example' => [
        'showMore' => true
    ],
    'anotherModule' => [
        ...
    ]
);
```

Setting configurations in Javascript:

```javascript
// Set config values for multiple modules
humhub.config.set({
    'myModule': {
        'myKey': 'value'
    },
    'moduleXY': {
        ...
    }
});

// Set a single value
humhub.config.set('myModule', {
    'myKey': 'value'
});

// You can also call
humhub.config.set('myModule', 'myKey', 'value');
```

> Warning: Since the configuration can easily be manipulated, you should not set values which can compromise the security of your application.

### Module Texts

Beside the `config` array, the module instance furthermore provides a `text` utility function for accessing texts  configurations.

```php
//Configurate your text in your php view.
$this->registerJSConfig('example', [
    'text' => [
        'error.notallowed' => Yii::t('ExampleModule.views_example', 'You are not allowed to access example!');
    ]
]);
```

Access your text as

```javascript
module.text('error.notallowed');

// which is a short form of:
module.config['text']['error.notallowed'];
```

### Module Log

Your module can be used to create module specific log entries by using the `module.log` utility.
The log object supports the following log level functions:

1. *trace* - For detailed trace output
2. *debug* - For debug output
3. *info* - Info messages
4. *success* - Used for success info logs
5. *warn* - Warnings
6. *error* - For error messages
7. *fatal* - Fatal errors

All log functions accept up to three arguments:

1. The actual message (or text key)
2. Details about the message this could be an js object an error or a client response object
3. A setStatus flag, which will trigger a global `humhub:modules:log:setStatus` event. This can be used to trigger the status bar for providing user feedback.

The following calls are valid:

```javascript
// Log config text 'error.notallowed' and give user feedback.
module.log.error('error.notallowed', true);

// In the following example we received an error response by our humhub.modules.client. 
// The response message will try to resolve a default message for the status of your response. 
// Those default messages are configured in the core configuration texts.
module.log.error(response, true);

// The error.default text message is available through the configuration of the log module see humhub\widgets\JSConfig
module.log.error('error.default', new Error('xy'), true);
```

The trace level of your module can be configured by setting the `traceLevel` configuration. If your module does not define an own trace level the log modules's traceLevel configuration will be used instread. In production mode the default log level is set to `INFO`, in dev mode its set to `DEBUG`.

> Info: Your module logger will try to resolve a given text key to a module or global text configuration.

> Info: The `module.log.success()` function will trigger a status bar update by default.

> Note: If you change the `traceLevel` of a module at runtime, you'll have to call `module.log.update()`.
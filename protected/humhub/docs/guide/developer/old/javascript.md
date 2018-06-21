Javascript API
=======

Since version 1.2, HumHub provides a module based Javascript API within the `humhub` namespace.
Instead of embeding inline script blocks into your view files, it's highly recommended to use the new module system for your modules frontend logic. 

## Modules

### Module Asset

Module script files should reside within the `resources/js` folder of your humhub module and should ideally be appended at the bottom of your document. This can be achieved by using [Asset Bundles](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html).


```php
namespace humhub\modules\example\assets;

use yii\web\AssetBundle;

class ExampleAsset extends AssetBundle
{
    // You can also use View::POS_BEGIN to append your scripts to the beginning of the body element.
    public $jsOptions = ['position' => \yii\web\View::POS_END];
    public $sourcePath = '@example/resources';
    public $js = [
        'js/humhub.example.js'
    ];
}
```

> Note: Make sure to add your assets after the core scripts, which are added within the html head.

> Note: Your Asset Bundle should reside in the `assets` subdirectory of your module.

In your view you can register your Asset Bundle by calling

```php
\humhub\modules\example\assets\ExampleAsset::register($this);
```

Where `$this` is the view instance. More infos about the use of Asset Bundles are available in the [Yii Guide](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html).

### Module Registration

Modules are added to the `humhub.modules` namespace by calling the `humhub.module` function.

```javascript
humhub.module('example', function(module, require, $) {
   ...
});
```
Example of a submodule:

```javascript
humhub.initModule('example.mySubmodule', function(module, require, $) {
...
}
```

The first argument of the `humhub.module` function defines the module id, which should be unique within your network. The second argument is the actual module function itself.

> Note: You should use an unique namespace for your custom modules as `myproject.mymodule` otherwise it may interfere with other modules.

Your module function will be called with the following arguments:

1. `module` - Your module instance, used for exporting module logic and accessing module specific utilities
2. `require` - Used for injecting other modules.
3. `$` - jQuery instance.

##### Module Exports

Module functions and attributes can only be accessed outside of the module if they are exported, either by directly appending them to the `module` object or by calling `module.export`.

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
##### Module Initialization

Your module's initialization logic can be implemented by exporting an `init` function. This function will automatically be called after the page is loaded. 

By default this function is only called once after a full page load (or directly after the registration if it was loaded per ajax). If your module requires a reinitialization also after Pjax page loads, your module has to set the  `initOnPjaxLoad` setting.

```javascript
module.initOnPjaxLoad = true;

var init = function($pjax) { 
    // Do some global initialization work, which needs to run in any case
    if($pjax) {
        // Runs only after a pjax page load
    } else {
        // Runs only after fresh page load
    }
}

module.export({
	init: init
});
```

##### Module Unload

For the purpose of cleaning up module related dom nodes etc. there is also an `unload` function, which is called before each Pjax page load. This function is mainly used to remove obsolete dom nodes in order to prevent memory leaks, remove obsolete dom listeners, or clear some module data.  

```javascript
var unload = function($pjax) { 
    $('.moduleResidues').remove();
}

module.export({
    unload: unload
});
```

##### Module Dependencies

Other modules can be injected into your module by using the `require` function. 

```javascript
// Import of the core client module.
var client = require('client');

//Calling myFunction within another module
require('example').myFunction();

//Calling myFunction within another module (full path)
require('humhub.modules.example').myFunction();

//Also a valid call
require('modules.example').myFunction();

//Calling myFunction outside of a module
humhub.modules.example.myFunction();
```

> Note: You should only require modules at the beginning of your own module, if you are sure the required module is already registered.

If your module requires other modules, which are not part of the core you can ensure the order by means of the `$depends` attribute of your Asset Bundle:


```php
public $depends = [
    'humhub\modules\anotherModule\assets\AnotherModuleAsset'
];
```

If you can't assure the module registration order for another  module, but need to require the module, you can either require it within your module function or use the `lazy` flag of the require function. 

The call of `require('anotherModule', true)` will return an empty namespace object, in case the module was not registered yet. The module logic will be available after the registration of the dependent module.

>Note: When using the `lazy` flag, you can't assure the required module will be initialized within your own module's `init` logic.

```javascript
humhub.initModule('example', function(module, require, $) {
    // We can't ensure the initial logic of module2
    var module2 = require('module2', true); 

    // at this point module2 might be empty
    
    var myFunction = function() {
        // myFunction should only be used outside of the init logic
        module2.executeSomeFunction();
    }
});
```

>Info: Since core modules are appended to the head section of your document, there shouldn't be any dependency problem.

### Module Configuration

If you need to transfer values as texts, settings or urls from your php backend to your frontend module, you can use the `module.config` array which is automatically available within your module as in the following example:

```javascript
humhub.initModule('example', function(module, require, $) {

    var myAction = function() {
        if(module.config.showMore) {
            // Do something
        }
        
    };
});
```

In your view you can set the module configuration as follows

```php
// Single module
$this->registerJsConfig('example', ['showMore' => true]);

// Multiple modules
$this->registerJsConfig([
    'example' => [
        'showMore' => true
    ],
    'anotherModule' => [
        ...
    ]
);
```

Setting configurations in javascript:

```javascript
//Set config values for multiple modules,
humhub.config.set({
    'myModule': {
        'myKey': 'value'
    },
    'moduleXY': {
        ...
    }
});

//Set single value
humhub.config.set('myModule', {
    'myKey': 'value'
});

//You can also call
humhub.config.set('myModule', 'myKey', 'value');
```

>Note: Since the configuration can easily be manipulated, you should not set values which can compromise the security of your application.

> TIP: Module setter are normally called within views or widgets to inject urls or translated text for user feedback or modals.

### Module Texts

Beside the configuration addition, the module instance does furthermore provide a `module.text` function for easily accessing texts of your configuration.

Example of an error text.

```php
//Configurate your text in your php view.
$this->registerJsConfig([
    'example' => [
        'showMore' => true,
        'text' => [
            'error.notallowed' => Yii::t('ExampleModule.views.example', 'You are not allowed to access example!');
        ]
    ]
);
```

Access your text within your module function as this

```javascript
module.text('error.notallowed');

// which is a short form of:
module.config['text']['error.notallowed'];
```

### Module Log

Your module is able to create module specific log entries by using the `module.log` object of your module instance.
The log object supports the following log level functions:

1. *trace* - For detailed trace output
2. *debug* - For debug output
3. *info* - Info messages
4. *success* - Used for success info logs
5. *warn* - Warnings
6. *error* - For error messages
7. *fatal* - Fatal errors

All log functions accept up to three arguments:

1. The actual message
2. Details about the message (or errors in case of warn/error/fatal)
3. A setStatus flag, which will trigger a global `humhub:modules:log:setStatus` event. This can be used to give user-feedback (status bar).

Instead of an actual message, you can also just provide a text key as the first argument. 
The following calls are valid:

```javascript
// Log config text 'error.notallowed' and give user feedback.
module.log.error('error.notallowed', true);

// In the following example we received an error response by our humhub.modules.client. The response message will try to resolve a default
// message for the status of your response. Those default messages are configured in the core configuration texts.
module.log.error(response, true);

// The error.default text message is available through the configuration of the log module see humhub\widgets\JSConfig
module.log.error('error.default', new Error('xy'), true);
```

> Info: Your module logger will try resolving your message string to a module or global text.

> Note: The success log will by default trigger a status log event.

The trace level of your module can be configured by setting the `traceLevel` of your module configuration. 
If your module does not define an own trace level the log modules's traceLevel configuration will be used. 

> Info: In production mode the default log level is set to `INFO`, in dev mode its set to `DEBUG`.

> Note: If you change the `traceLevel` of a module at runtime, you'll have to call `module.log.update()`.

## Core Modules
### Config Module

Beside the `module.config` utility you can also use the global configuration as follows

```javascript
// Retrieves the whole config object of 'myModule'
var moduleConfig = require('config').get('myModule');
var myValue = config['myKey'];

//Single value getter with default value
var myValue = humhub.config.get('myModule', 'myKey', 'myDefaultValue');
```

With `humhub.config.is`, you are able to check if a value is true

```javascript
//Check if the configkey enabled is true, default false
if(humhub.config.is('myModule', 'enabled', 'false')) {
    ...
}
```
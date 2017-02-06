Javascript API
=======

Since version 1.2 HumHub provides a module based Javascript API within the `humhub` namespace.
Instead of embeding inline script blocks into your view files, it's highly recommended to use the new module system for your modules js scripts. 

The usage of this API and it's core components are described in the following.


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

### Write Custom Modules

Module are registered by calling the `humhub.module` function.

```javascript
// After registration, all exported functions will be available under the namespace humhub.modules.example
humhub.module('example', function(module, require, $) {
   ...
});
```
Example of a submodule:

```javascript
// Submodules can be registered as following
humhub.initModule('example.mySubmodule', function(module, require, $) {
...
}
```

The first argument of the `humhub.module` function defines the module id, which should match with your backend module id. The second argument is the actual module function itself.

Your module function is provided with the following arguments:

1. `module` - Your module instance, used for exporting module logic and accessing module specific utilities as log, text, config
2. `require` - Used for injecting other modules.
3. `$` - jQuery instance.

##### Module Exports

Module functions and attributes can only be accessed outside of the module if they are exported, either by directly appending them to the `module` object or by calling `module.export`.

```javascript
humhub.module('example', function(module, require, $) {
   
    // export public function
    module.myPublicFunction = function() {/* ... */}

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

By default this function is only called once after a fresh page load (or directly after the registration if it was loaded per ajax). If your module requires an initialization also after Pjax page loads, your module has to set the  `initOnPjaxLoad` setting.

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

For the purpose of cleaning up module related dom nodes etc. there is also an `unload` function, which is called before each Pjax page load. This function is mainly used to obsolete dom nodes to prevent memory leaks, remove obsolete dom listeners, or clear some module data.  

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
// Add this to your ExampleAsset.php file
public $depends = [
    'humhub\modules\anotherModule\assets\AnotherModuleAsset'
];
```

If you can't assure the module registration order for another  module, but need to require the module, you can either require it within your module function or use the `lazy` flag of the require function. 

The call to `require('anotherModule', true)` will return an empty namespace object, in case the module was not registered yet. After the registration of the dependent module, the module will be available.

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

>Info: All core modules are appended to the head section of your document. So der should not be any dependency problem.

### Module Configuration

If you need to transfer values as texts, flags or urls from your php backend to your frontend module, you can use the `module.config` array which is automatically available
within your module function as in the following example:

```javascript
humhub.initModule('example', function(module, require, $) {
...

    var myAction = function() {
        if(module.config['showMore']) {
            // Do something
        }
        
    };
});
```

In your php view you can set the module as follows

```php
// Single module
$this->registerJsVar('example', ['showMore' => true]);

// Multiple modules
$this->registerJsVar([
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
> TIP: Module setter are normally called within views or widgets to inject urls or translated text for errors or modals

### Module Texts

Beside the configuration addition, the module instance does furthermore provide a `module.text` function for easily accessing texts of your configuration.

Example of an error text.

```php
//Configurate your text in your php view.
$this->registerJsVar([
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

1. trace - For detailed trace output
2. debug - For debug output
3. info - Info messages
4. success - Used for success info logs
5. warn - Warnings
6. error - For error messages
7. fatal - Fatal errors

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

> Info: The core ui.status module is responsible for triggering the user result.

> Note: The success log will by default trigger a status log event.
```

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

## Client

The `humhub.modules.client` module provides some utilities for calling backend actions. The client is build on top of
jquery's `$.ajax` function and provides some additional functionality as a response wrapper, promises and enhanced error handling. 
A backend action can be called by `client.ajax`, `client.get` or `client.post`.

The client module can be used as follows

```javascript
var client = require('client');

//Simple ajax call
client.ajax(url, {
    data: {
        id: myModelId,
        type: 'POST'
    }
}).then(function(response) {
    handleSuccess(response.content);
}).catch(function(errResponse) {
    handleError(errResponse);
});

//The same call with forcing a post call
client.post(url, {
    data: {
        id: myModelId
    }
}).then(function(response) {
    handle(response.content);
}).catch(function(errResponse) {
    handleError(errResponse.getErrors());
});

// The status function can be used to react to specific response status codes
client.post(url, cfg)
.status({
    200: function(response) {
        // Success handler with user feedback
        $('container').html(response.output);
        module.log.success('success.edit', true);
    },
    400: function(response) {
        // Validation error user feedback is given by validation errors
        $('container').html(response.output);
    }
}).catch(function(e) {
    // Unexpected error with user feedback
    module.log.error(e, true);
});
```
> Note: Since Yii urls can't be created on client side, you'll have to inject them through data attributes or the module config.

> TIP: The `action` mechanism described later, facilitates the mapping of urls and action handlers.

### Response Wrapper

The response object returned by your client contains the following attributes:

 - url: the url of your call
 - status: the result status of the xhr object
 - response: the server response, either a json object or html depending of the 'dataType' setting of your call.
 - textStatus: In case of error: "timeout", "error", "abort", "parsererror", "application"
 - dataType: the datatype of your call
 - error: ajax error info
 - validationError: flag which is set if status = 400
 - If your response is of type 'json' all your json values will also be directly appended to the response object's root.

## Actions (TBD)

The `humhub.modules.action` module can be used to define frontend actions, which are triggered by events like clicking a button or changing an input field.
The action mechanism provides an unified way of binding actions to your ui components.

The following example binds all click event of a button to the `myAction` function of your module.

```html
<!-- In your view file -->
<a href="#" data-action-click="example.myAction" data-action-url="<?= ... ?>">Call my action!</a>
```

```javascript
// within your module
var myAction = function(evt) {
    
    client.get(evt).then(function(response) {
        ...
        evt.$trigger.text(response.output);
        module.log.success('success.');
    }).catch(function(response) {
        ...
        module.log.error(response, true);
    });
}

...

module.export({
    ...
    myAction: myAction
});
```

> Note: don't forget to export your action handler, otherwise they won't be accessible.

### Action Binding

The `humhub.modules.action.bindAction` function is used to bind event types to nodes of a given selector. 

The following event type action bindings are available by default:

```javascript
// This line adds the action behavior for all elements with a data-action-click attribute for 'click' events.
this.bindAction(document, 'click', '[data-action-click]');
this.bindAction(document, 'dblclick', '[data-action-dblclick]');
this.bindAction(document, 'change', '[data-action-change]');
```

You can extend the supported event types with custom types as in the following example:

```javascript
// This line adds the action behavior for all elements with a data-action-click attribute for 'click' events.
this.bindAction(document, 'customevent', '[data-action-customevent]');
```

__How does it work:__

In the previous examples the bindAction call will bind a delegate to the `document` e.g. `$(document).on('click', '[data-action-click]', function() {...});`
If the delegate receives an unhandled action event, it will rebind all bindings directly to the trigger elements and run the action.
All upcoming events will directly be handled by the trigger, which prevents the bubbling latency.

### Action Event

All action-handlers are provided with an action event which is a derivate of `$.Event` and provides, beside others, the following attributes:

- `$trigger`: The jquery $trigger object, which was responsible for triggering the event e.g. a button.
- `$target`: Can be set by the data-action-target attribute of your $trigger, by default the $trigger is also the event target. See the action component section for more details.
- `$form`: In case your $trigger is of `type="submit"` or has a `data-action-submit` attribute, the action event will include a jquery object of the sorrounding form.

```html
<form ...>
    ...
    <!-- The url of your action handler will either take the form action url or the data-action-url/data-action-click-url (which will be prefered) -->
    <button data-action-submit data-action-click="example.submit">Submit</button>
</form>
```

```javascript
//The client knows how to handle action events, so you just have to call the following to submit evt.$form
var submit = function(evt) {
    client.submit(evt).then(...).catch(...);
}
```

- `url`: Contains the `data-action-url` (used for all actions of $trigger) or `data-action-click-url` (will be prefered in case of click events)
- `params`: Can be used to add additional action parameters by setting `data-action-params` or the more specific `data-action-click-params` for click events

```html
    <button data-action-click="example.call" data-action-params="{type:'example'}">Call Action!</button>
```

```javascript
var call = function(evt) {
    alert(evt.params.type);
}
```

 - `originalEvent`: The original event which triggered the action

### Action Handlers

There are different types of action-handlers:

- __Direct__ action-handlers can be directly passed to the `bindAction` function.
- __Registered__ action-handler are registered by the `registerHandler` and can be shared by modules.
- __Component__ action-handlers are used to execute actions of a ui component (see Action Components) .
- __Namespace__ action-handlers will be searched within the humhub namespace if there is no other matching handler.

Example of a `direct-handler`:

```html
<!-- Somewhere in my view -->
<div id="#myContainer">
    <!-- Note, you won't have to define the name of your handler in this case -->
    <button class="sendButton" data-action-url="<?= Url::to(...) ?>">Send</button>
</div>
```

```javascript
//Somewhere in myModule
var action = require('action');

//Bind a click handler to all .mySpecialButtons within  #myContainer
action.bindAction('#myContainer', 'click', '.sendButton', function(evt) {
    //this within a handler function always points to the triggered jQuery node
    client.post(evt).then(function(resp) {...});
});
```
> TIP: Since humhub action binding is based on jquerys event delegation, you can use all event types of jquery.

> NOTE: The first argument of the bindAction should be the first static (never removed from dom or lazy loaded) parent node of all nodes you wish to bind. Too many delegated events to the `document` is a performance antipattern.

Example a `namepace-handler`:

```html
<!-- A click to this button will execute the exported myFunction of myModule as defined above -->
<button data-action-click="myModule.myFunction">Do something !</button>
```
> TIP: If you have multiple actions with different action urls you can specify `data-action-click-url`, `data-action-change-url`.

### Components

Action components can be used to connect specific dom sections to a action component class. The root of a component is marked with a ´data-action-component´ attribute. 
This data attribute contains the component type e.g. `tasks.Task`. The component class must be dereived from ´humhub.modules.action.Component´.
Action components can be cascaded to share data between a container and entry components e.g. a `tasks.TaskList` contains multiple `tasks.Task` entries. 
The TaskList can provide action urls for all its Task entries and provide additional actions.
For this purpose the components `data` function can be used to search for data values which are either directly set on the component itself or a parent component. 

Example:

```html
<!-- Start of container component TaskList with given data values needed by its entries -->
<div id="taskContainer" data-action-component="tasks.TaskList" 
        data-content-edit-url="<?= $contentContainer->createUrl('/tasks/task/edit') ?>"
        data-content-delete-url="<?= $contentContainer->createUrl('/tasks/task/delete') ?>">
    
     <!-- Will execute tasks.TaskList.create on click -->
    <a data-action-click="create">Create new</a>
    
    <!-- First Task entry with data-content-key -->
    <div class="task" data-action-component="tasks.Task" data-content-key="<?= $task->id ?>">
        ...
        <button data-action-click="edit">Edit</button>
        <button data-action-click="delete">Delete</button>
    </div>
    ...
</div>

<!-- By using data-action-target you can run component actions outside of a component -->
<button data-action-click="update" data-action-target="#taskContainer">Update</button>
```

(TBD: component class example)

> TIP: If you want to handle content models as posts which are extending [[humhub\modules\content\components\ContentActiveRecord]] you should extend the content-component described in the next section!

### Content Components

One of the main tasks of HumHub is the manipulation (create/edit/delete) of content entries as posts, wikis and polls. The `humhub.modules.content` module provides an
interface for representing and handling content entries in the frontend. The following module implements a task module with an Task content component and a Tasklist content component.
If your content class supports the actions edit and delete, it will have to set a data-content-key attribute with the given content id. This is not necessary if your
implementation does not support these functions as in the TaskList example.

The ´Content´ component class provides the following actions by default:

 - ´delete´ deletes a content object by using a confirm modal
 - ´edit´ edits the given content by loading a edit modal
 - ´create´ used to create new content

```javascript
//Initializing the tasks module
humhub.initModule('tasks', function(module, require, $) {
    var Content = require('content').Content;
    var object = require('util').object;
    
    var Task = function(id) {
        Content.call(this, id);
    };
    
    object.inherits(Task, Content);
    
    var TaskList = function(id) {
        Content.call(this, id);
    };
    
    object.inherits(TaskList, Content);
    
    TaskList.prototype.create = function() {
        var that = this;
        //Most controller use the same form for edit and create!
        this.edit().then(function(response) {
            //This is executed when the creation of the new model is done
            that.appendOpen(response.getContent());
            return true;
        });
    };
    ...

    //Dont forget to export your content types !
    modules.export({
        Task: Task,
        TaskList, TaskList
    });
}
```

__How does it work:__

1. An action-event-handler is bound to all dom nodes with a `data-action-click` on startup. 
2. When triggered, the action event handler does check if a direct handler was provided.
2. If not it will try to call `Component.handleAction`.
3. If this handler does find a sorrounding component it will instantiate the component and try to execute the given handler.
4. If no other handler was found, the handler will try to find a handler in the humhub namespace.

The content-action-handler for actions like delete/edit need to lookup an action url this can either be done by adding a data-action-url/data-action-url-click directly to the trigger node
or by adding data-content-edit-url/data-content-delete-url to the component root or a parent component root. A direct trigger assignment will overwrite a direct component assignment, 
which will overwrite the setting of a parent data-content-base.

> TIP: If your content does not need to overwrite the defaults or provides some additional actions (Like the Task in the example) you can just set ´content.Content´ as ´data-action-component´.

> TIP: beside the default handler the content can define other handler by simply adding it to the content prototype

## Additions

## Stream

## Modal

## Util
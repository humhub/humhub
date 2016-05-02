Javascript frontend
=======

HumHub provides a simple Javascript module system, which enables a similar module structure as the backend.
Instead of using inline code blocks in php, a module (especially complex modules) should add it's frontend logic
as a javascript module to the HumHub module system. The module system provides, an event system for event driven
communication, module configuration, server communication utilities and more under the global namespace `humhub`.

## Core
### Module Registration

Modules can be registered by calling the `humhub.initModule` function. This function
accepts an id and the actual module function and will add the given module to the namespace `humhub.modules`. 

The module function is provided with three arguments:

- The __module__ object is used to export functions either by appending functions/properties directly to `module` or by calling `module.export`.
- The __require__ function can be used to inject other modules.
- __$__ a references jquery

Modules can export a `init` function, which is called automatically after the document is ready.
The following example shows the implementation of a dummy module `humhub.modules.myModule`.

```javascript
//Initialization of myModule
humhub.initModule('myModule', function(module, require, $) {
    //Require at client module at startup
    var client = require('client');

    //Private property
    var myProperty;

    //Definition of an exported object
    module.myPublicObject = {};

    //Export some other functions
    module.export({
        myFunction: function() {
            ...
        },
        init: function() {
            //This code will automatically be executed when dom is ready.
        }
    });
});

...

//Calling myFunction within another module
require('myModule').myFunction();

//Calling myFunction within another module (full path)
require('humhub.modules.myModule').myFunction();

//Also a valid call
require('modules.myModule').myFunction();

//Calling myFunction outside of a module
humhub.modules.myModule.myFunction();
```

> NOTE: If a module requires another module at startup the required module has to be initialized before. The init order of core modules is configured in the Gruntfile.js

> TIP: You can require modules at runtime by calling `require` within exproted functions, this can solve potential startup dependency issues.

> TIP: Code that requires the page to be loaded (dom access, dom event delegation) should be pushed to the `init` function

> NOTE: Module functions should only be called by other modules, since most of them require an initialization.

### Module Config

The HumHub javascript core provides a mechanism to configure modules. A moduleconfig can be set by calling `humhub.config.set`,

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
> TIP: Module setter are normally called within views or widgets to inject urls or translated text for errors or modals

Modules can retrieve its config through `humhub.config.get`

```javascript
//Retrieves the whole config object of 'myModule'
var config = humhub.config.get('myModule');
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
jquery and provides some additional functionality as a response wrapper and enhanced error handling. A backend action can
be called by `client.ajax` or `client.post`. Both functions expect an url and jquery like ajax configuration.

```javascript
var client = require('client');

//Simple ajax call
client.ajax(url, {
    data: {
        id: myModelId,
        type: 'POST'
    }
}).then(function(response) {
    handle(response.content);
}).catch(function(errResponse) {
    handleError(errResponse.getErrors());
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
```
> Note: Since Yii urls can't be created on the client side, you'll have to inject them through data attributes or the module config.

> TIP: The action module provides an uniform way of registering ajax actions without the need of calling the client itself.

### Response Wrapper

(TBD)

## Actions

The `humhub.modules.action` module can be used to define frontend actions, which are triggered by events like clicking a button or changing an input field.

### Action Binding

The `humhub.modules.action.bindAction` function can be used to bind an action-handler to a specific event type (e.g. click, change,...) of nodes.

There are different types of action-handlers:

- __Direct__ action-handlers can be directly passed to the `bindAction` function.
- __Registered__ action-handler are registered by the `registerHandler` or `registerAjaxHandler` and can be shared by modules.
- __Content__ action-handlers are used to execute content related actions (see Content) .
- __Namespace__ action-handlers will be searched within the humhub namespace if there is no other matching handler.

Example of a `direct-handler`:

```html
<!-- Somewhere in my view -->
<div id="#myContainer">
    <button class="mySendButton" data-action-url="<?= Url::to(...) ?>">Send</button>
</div>
```

```javascript
//Somewhere in myModule
var action = require('action');

//Bind a click handler to all .mySpecialButtons within  #myContainer
action.bindAction('#myContainer', 'click', '.mySendButton', function(evt) {
    //this within a handler function always points to the triggered jQuery node
    client.post(this.data('action-url').then(function(resp) {...});
});
```
> TIP: Since humhub action binding is based on jquerys event delegation, you can use all event types of jquery.

> TIP: In case of direct action-handlers, there is no need to define a action-handler like data-action-click="myHandler" on the trigger element.

> NOTE: The first argument of the bindAction should be the first static (never removed from dom or lazy loaded) parent node of all nodes you wish to bind. Too many delegated events to the `document` is a performance antipattern.

Example registered `ajax-handler`:

```html
<!-- Somewhere in my view -->
<button data-action-click="humhub.modules.myModule.sendAjax" data-action-url="<?= Url::to(...) ?>">Send</button>
```

```javascript
//Somewhere within myModule
action.registerAjaxHandler('humhub.modules.myModule.sendAjax', {
    success: function(response) {
        //My success handler
    },
    error: function(response) {
        //My error handler
    }
}

//No need to call bindAction, since data-action-click nodes are bound automatically
``

Example a `namepace-handler`:

```html
<!-- A click to this button will execute the exported myFunction of myModule as defined above -->
<button data-action-click="humhub.modules.myModule.myFunction">Do something !</button>
```

> TIP: The action handler will determine the action url and execute the provides success/error handler automatically

> TIP: If you have multiple actions with different action urls you can specify `data-action-url-click`, `data-action-url-change`,... 
data-action-url is always used as fallback

> TIP: The action module binds some default actions like click, dbclick and change to nodes with a data-action-<type> attribute, so these event types do not have to be bound manually.

### Components

Action components can be used to connect specific dom sections to a javascript action component class. The root of a component is marked with a ´data-action-component´ assignment. This data attribute
contains the component type e.g `humhub.modules.tasks.Task` or short `tasks.Task`. The component class must be dereived from ´humhub.modules.action.Component´.
Action components can be cascaded for to share data between a container and entry components e.g. a `tasks.TaskList` contains multiple `tasks.Task` entries. 
The TaskList can provide action urls for all its Task entries and provide additional actions.
For this purpose the components `data` function can be used to search for data values which are either set on the component root itself or a parent component root. 

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
```

> TIP: If you want to handle content models as posts which are extending [[humhub\modules\content\components\ContentActiveRecord]] you should extend the content-component described in the next section!

### Content

One of the main tasks of HumHub is the manipulation (create/edit/delete) of content entries as posts, wikis and polls. The `humhub.modules.content` module provides a
interface for representing and handling content entries on the frontend. The following module implements a task module with an Task content component and a Tasklist content component.
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
2. When triggered the action-event-handler does check if a direct handler was provided
2. If not it will try to call `Component.handleAction`
3. If this handler does find a sorrounding component it will instantiate the component and try to execute the given handler.
4. If no other handler was found, the handler will try to find a handler in the humhub namespace.

The content-action-handler for actions like delete/edit need to lookup an action url this can either be done by adding a data-action-url/data-action-url-click directly to the trigger node
or by adding data-content-edit-url/data-content-delete-url to the component root or a parent component root. A direct trigger assignment will overwrite a direct component assignment, 
which will overwrite the setting of a parent data-content-base.

> TIP: If your content does not need to overwrite the defaults or provides some additional actions (Like the Task in the example) you can just set ´content.Content´ as ´data-action-component´.

> TIP: beside the default handler the content can define other handler by simply adding it to the content prototype

## Additions

## Modal

## Util
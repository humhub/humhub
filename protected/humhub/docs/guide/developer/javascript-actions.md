Javascript Actions
=======

The `humhub.modules.action` module provides a machanism for binding Javascript action handlers to dom events as the click of a button.

The following example binds the action `example.myAction` to `click` events of a button.
The action handler uses the `client` module for calling an url defined by `data-action-url`. 

###### View:

```php
<button data-action-click="example.myAction" data-action-url="<?= $myActionUrl ?>">Call my action!</button>
```

###### Module:

```javascript
var myAction = function(evt) {
    client.get(evt).then(function(response) {
        //evt.$trigger is the button itself in form of a jQuery instance
        evt.$trigger.text(response.output);
        module.log.success('Done!');
    }).catch(function(e) {
        module.log.error(e, true);
    });
}

module.export({
    myAction: myAction
});
```

> Note: Don't forget to export your action handler, otherwise it won't be accessible.

### Action Handlers

There are different types of action handlers:

- **Direct** action handlers are directly passed to the `bindAction` function (see section Action Binding Mechanism).

###### View:
```html
<div id="#myContainer">
    <!-- Note, you won't have to define the name of your handler in this case -->
    <button class="sendButton" data-action-url="<?= Url::to(...) ?>">Send</button>
</div>
```

###### Module:
```javascript
// Bind a click handler to all .sendButton nodes within  #myContainer
require('action').bindAction('#myContainer', 'click', '.sendButton', function(evt) {
    client.post(evt).then(function(resp) {...});
});
```
- **Registered** action handlers are globaly registered by means of the `registerHandler` function and can be shared by modules.

###### View:
```html
<button data-action-click="myRegisteredHandler">Magic!</button>
```

###### Module:
```javascript
require('action').registerHandler('myRegisteredHandler', function(evt) {/*...*/});
```
- **Component** action handlers are used to execute actions of a ui  [Components](javascript-components.md)
- **Namespace** action handlers will be searched within the humhub namespace if there is no other matching handler

```html
<!-- A click to this button will execute the exported myFunction of myModule -->
<button data-action-click="myModule.myFunction">Do something !</button>
```

> Note: **Component** and **Namespace** action handlers are the most common and prefered handler types.

> TIP: You can define multiple actions with different urls on the same $trigger by means of for example `data-action-click-url` and `data-action-change-url`.

### Action Event

All action handler functions are provided with an action event which is a derivate of `$.Event` and provides, beside others, the following attributes:

- `$trigger`: The jquery instance, which was responsible for triggering the event e.g. a button.
- `$target`: Can be used to define a target component or widget and is defined by the `data-action-target` attribute of $trigger. If not explicitly set, the $trigger node will also be the events $target. See the [component](javascript-components.md) section for more details. 
- `url`: Contains the `data-action-url` or `data-action-click-url` (will be prefered in case of click events).
- `params`: Can be used to add additional action parameters by setting `data-action-params` or the more specific `data-action-click-params` in case of a click event.

###### View:
```html
<button data-action-click="example.someAction" data-action-params='{"type":"example"}'>Call Action!</button>
```

###### Module:
```javascript
var someAction = function(evt) {
    alert(evt.params.type);
}
```
- `$form`: In case your $trigger is of `type="submit"` or has a `data-action-submit` attribute, the action event will include a jquery instance of the sorrounding form or the form set by the $target.

###### View:
```php
<?php $form = ActiveForm::begin(); ?>
    <!-- ... Form Inputs ... -->
    <button type="submit" data-action-click="example.submit" data-action-url="<?= $url ?>">Submit</button>
<?php ActiveForm::end(); ?>
```

###### Module:
```javascript
var submit = function(evt) {
    client.submit(evt).then(...).catch(...);
}
```
> Info: The `client` module knows how to handle action events and will try to determine the url from the given event instance if no url was explicitly provided as argument. In case of `client.submit`, the client will try to determine the url of the forms `action` if the trigger does not specify an action url.

 - `originalEvent`: The original event which triggered the action
 - `finish`: This function is called to mark the action as completed, this function may be called manually to release the action block or remove the loader animation of a trigger with `data-ui-loader` flag. See the Action Blocking section for more information.

### Action Blocking

To prevent actions from beeing executed multiple times before finishing, actions are blocked during the execution time by default. The blocking logic can be configured by setting the `data-action-block` on the trigger node. The following block values are available:

- *none*: No blocking at all
- *sync*: Synchronous blocking, the block will be released after the handler finished. This is the default block for all non async trigger elements.
- *async*: The block has to be released manually by calling the `event.finish()` function. Note this block type is  used by default for action handlers with a given `data-action-url`, `data-action-submit` or `type="submit"` trigger.

> Note: `client` calls as `client.get(evt)` or `client.submit(evt)` will call the events `finish` function automatically after receiving the server response, so you won't have to call it in your handler.

### Action Binding Mechanism

The `humhub.modules.action.bindAction` function is used to bind event types to all nodes of a given selector. 

The following action bindings are available by default:

```javascript
this.bindAction(document, 'click', '[data-action-click]');
this.bindAction(document, 'dblclick', '[data-action-dblclick]');
this.bindAction(document, 'change', '[data-action-change]');
```

Currently only `click`, `dbclick`, `change` events are supported by default. This may change in the future.
You can extend the supported event types on demand as in the following example:

```javascript
require('action').bindAction(document, 'customevent', '[data-action-customevent]');
```
> NOTE: The first argument of the bindAction should be the first static (never removed from dom or lazy loaded) parent node of all nodes you wish to bind. 

> NOTE: Too many delegated events to the `document` is a performance antipattern.

**How does it work:**

In the previous example the bindAction call will bind a [delegate](https://learn.jquery.com/events/event-delegation/) to the `document`: 
```javascript
$(document).on('customevent', '[data-action-customevent]', function() {...});
```
If the delegate handler receives an unhandled action event, it will rebind all bindings directly to the trigger elements and run the action.
All upcoming events will directly be handled by the trigger, which prevents the bubbling latency.

> NOTE: As long as you don't need any custom bindings, you won't have to worry about the binding mechanism.

> TIP: Since humhub action binding is based on jquerys event delegation, you can use all event types of jquery.
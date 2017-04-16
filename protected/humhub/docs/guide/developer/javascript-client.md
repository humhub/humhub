Javascript Client
=======

The `humhub.modules.client` module provides some utilities for calling backend actions. The client logic is build on top of jquerys `$.ajax` function and provides some additional functionality as a response wrapper, promises and enhanced error handling. 

## Getting started

The client module provides the following functions for calling backend actions:

 - `post` - Send **POST** data to the server
 - `get` - Send a **GET** request to the server
 - `html` - Load **HTML** from server by **GET**
 - `submit` - Submit a form by **POST**

> Note: By default all client functions except `html` are expecting `dataType: json` from the server. This behaviour can be changed by means of the ajax options.

The client module can be used as follows

```javascript
var client = require('client');

// Simple ajax call
client.ajax(url, {type: 'POST',data: { id: myModelId }})
.then(handleSuccess)
.catch(function(e) {
    module.log.error(e, true);
});

// The same call but forcing a post call
client.post(url, {data: {id: myModelId}})
.then(handleSuccess)
.catch(function(e) {
    module.log.error(e, true);
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
    module.log.error(e, true);
});
```
The mentioned functions can handle the following argument combinations:

```javascript
// Only provde ajax options (with url included)
client.get({...}).then(...); 

// Only provide url
client.get(url).then(...);

// Url and options
client.get(url, {...}).then(...);

// Can be called within an action function whereas the event holds the url
client.get(evt).then(...);

// Provide action event and additional ajax options
client.get(evt, {...}).then(...);

```

The `submit` function requires either an form selector, form jquery node or an action event as first argument.

```javascript
client.submit($('#myForm'), {...}).then(...);

client.submit('#myForm', {...}).then(...);

client.submit(evt).then(...);
```
> Note: Since Yii urls can't be created on client side, you'll have to inject them through data attributes or the module config.

> Note: You should always add an `catch` handler, otherwise the actual error will be swallowed by the promise framework.

### Response Wrapper

The response object returned by your client contains the following attributes:

 - `url`: the url of your call
 - `status`: the result status of the xhr object
 - `response`: the server response, either a json object or html depending of the 'dataType' setting of your call.
 - `textStatus`: In case of error: "timeout", "error", "abort", "parsererror", "application"
 - `dataType`: the expected response dataType of your call
 - `html`/`xml`: the response depending on dataType

If the response dataType is set to `json` (default) and the server acutally returns json the response attributes will be accessible directly through the response wrapper object as in the following example

**Backend**

```php
public function actionTest() {
    return $this->asJson(['success' => 'true']);
}
```

**Frontend**

```javascript
client.get(url).then(function(response) {
    alert(response.success)
}).catch(function(e) {
    module.log.error(e, true);
});
```


In case of an error (**catch**) the response furthermore contains

 - *error*: parsed error returned by the server
 - *errorThrown*: The textual portion of the HTTP status, such as "Not Found" or "Internal Server Error." 
 - *validationError*: is set to true if response status is 400

## Pjax

The [Pjax](http://pjax.herokuapp.com/) library is used by HumHub to enable partial page loads while maintaining the browser history. The use of Pjax provides major performance benefits in contrast to full page loads, since assets as stylesheets and javascript files only have to be loaded once. Furthermore only a part of the layout has to be rendered on the serverside and transfered over the network.

As of HumHub Version 1.2 [Pjax](http://pjax.herokuapp.com/) is enabled by default and modules and themes should be held compatible with the single page approach. 

##### Disable Pjax

You can disable Pjax by setting the following parameter in your `protected/config/common.php`

```php
return [
  'params' => [
    'enablePjax' => false
  ]
]

```

##### Disable Pjax for specific links

If Pjax is enabled, all regular links on your site will be attached with a pjax handler by default.
The Pjax behaviour is not attached to links with the following attributes:

 - *data-pjax-prevent* - explicit pjax prevent flag
 - *target* - links with html target attributes
 - *data-target*/*data-toggle* - bootstrap helper attributes

##### Events

Beside using the `initOnPjaxLoad` flag and `unload` function mentioned in the [Module Guide](javascript-index.md) you can also listen to the following global events:

 - `humhub:modules:client:pjax:beforeSend` - is triggered before pjax page loads
 - `humhub:modules:client:pjax:success` - is triggered after a pjax page load

> Note: Since javascript files in Yii are only loaded and executed once per full page load, some modules may have to be reinitialized after a pjax request. As mentioned in the [Module Guide](javascript-index.md) the modules `init` function is called in case of a pjax request only if the modules `initOnPjaxLoad` setting is set to `true`.

## Reload the current page

The `client.reload` function should be used in case a page reload is required. If `Pjax` is enabled, a `Pjax` based page reload will be triggered unless the `preventPjax` flag is set to true.

```javascript
// Will trigger a Pjax reload if enabled
client.reload();

// Will force a full page reload
client.reload(true);
```
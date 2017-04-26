Modals
=======

HumHub Modals are based upon [Bootstrap Modals](http://getbootstrap.com/javascript/#modals) and can be used to render forms or user feedback as confirmation requests or other information. The Javascript module `ui.modals` provides some additional functaionality for creating and loading modals.

###### Global Modal

By default an empty global modal with the id `globalModal` is available in the default HumHub layout.
The instance for this modal is available under the namespace `ui.modal.global` and can be reused by other modules at any time as follows

```javascript
var modal = require('ui.modal');

// load the result of someUrl into the global modal
modal.global.load(someUrl);
```


###### Create new Modals:

In most cases the global modal should be sufficient for your usecases. In some cases you'll need to create an independent modal which can be achieved by using the `modal.get()` function, which expects an modal id as fist argument and will search for the existence of a modal with the given id and will create a new one in case there is no such an modal.

```
var myModal = modal.get('myModalId');
myModal.load(someUrl);
```

You can also manipulate your modal content by means of the following functions:

- `setDialog(content)`: sets the dialog part of your modal, the `content` should be a `string`, `node` or `client.Response`. In case of a response instance, it should either be an `html` response or a `json` response with `output` markup.
- `setBody(content)`: sets the body part of your modal
- `setContent(content)`: sets the content part of your modal 
- `setHeader(content)`: sets the modal header title
- `setFooter(content)`: setts the modal footer
- `set(options)`: sets the modal options `header`, `body`, `content`, `footer` and bootstrap modal options (e.g. `backdrop`, `keyboard`.

> Note: Since Modals are derived from [ui.widget.Widget](javascript-components.md) you can also configure your custom modal by using `data-*` options.

### Load Remote Modal

To load remote content into your modal you can either manually load it by calling the `load` function of your modal or use the `load` action of the modal module.


#### Modal load

A call to your modals `load(url, options, originalEvent)` loads the result of `url` into your modal by means of a `GET` request. The response markup has to be the `modal-dialog` part of the modal. 

```javascript
modal.global.load(url).then(function(response) {
    // Called after your modal was filled with the response
}).catch(function(e) {
    module.log.error(e, true);
});
```
###### Returned view:

```php
<?php ModalDialog::begin(['header' => 'My Title']) ?>
    <div class="modal-body"><!-- ... --></div>
    <div class="modal-footer"><!-- ... --></div>
<?php ModalDialog::end() ?>
```

> Info: By default the `load` function expects `dataType: html`, if you require to return `json` content instead, your server response has to provide an `output` part with the rendered modal dialog.

#### Modal load action

If you just want to trigger a simple modal load event after clicking a button, you can use the `ui.modal.load` action as follows

```php
<!-- loads the result of $someUrl to the global modal -->
<button data-action-click="ui.modal.load" data-action-url="<?= $someUrl ?>">Load my Modal!</button>

<!-- loads the result from $someJsonUrl and insert the response.output to the global modal -->
<button data-action-click="ui.modal.load" data-action-url="<?= $someJsonUrl ?>" data-type="json">Load my Modal!</button>

<!-- loads the result of $someJsonUrl and insert the response.output to a custom modal -->
<button data-action-click="ui.modal.load" data-action-url="<?= $someJsonUrl ?>" data-modal-id="myId">Load my Modal!</button>
```

> Tip: If your button is rendered within your custom modal itself you can omit the data-modal-id setting.

#### Modal post

Similar to the load function you can use the `post` modal function or action to submit a post request.

```javascript
modal.global.post(url, {data: {somePostData: 'value' } });
```

or

```php
<button data-action-click="ui.modal.post" data-action-url="<?= $someUrl ?>">Post Modal!</button>
```

#### Modal submit

To render the result of a form submit into the global modal, your submit button should use the `ui.modal.submit` action on your submit button. This action will use the `client.submit` to submit the given form and set the result into the global modal.

### Modal Confirm

By using the `modal.confirm()` function, you can request a user confirmation as follows

```javascript
var options = {
    header: 'Please confirm this action!'
    body: 'Do you really want to execute this action?',
    confirmText: 'Yes',
    cancelText: 'No'
}

modal.confirm(options).then(function(confirmation) {
    if(confirmation) {
        //do something
    }
});
```

> Note: You do not have to provide all texts, there are default texts for your confirm modal.

#### Action confirm

```php
<button data-action-click="someAction" 
        data-action-confirm="Do you really want to execute this action?"
        data-action-confirm-header="Please confirm this action!"
        data-action-confirm-text="Yes"
        data-action-cancel-text="No">
    Do something!
</button>
```

### Render Modal
### Close Modal
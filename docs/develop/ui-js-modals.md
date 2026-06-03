# Modals

HumHub modals build on [Bootstrap modals](https://getbootstrap.com/docs/5.3/components/modal/). The `ui.modal` JavaScript module adds loading, posting and confirmation helpers on top.

## The global modal

The default HumHub layout includes an empty modal with id `globalModal`. Its instance is available at `ui.modal.global` and can be reused from any module:

```js
var modal = require('ui.modal');

modal.global.load(someUrl);
```

Reusing the global modal is the right default — only create your own when you need two modals visible at once.

## Creating your own modal

`modal.get(id)` returns the existing modal for `id` or creates a new one:

```js
var myModal = modal.get('myModalId');
myModal.load(someUrl);
```

A modal exposes setters for each region:

| Method                       | What it sets                                                                                            |
|------------------------------|---------------------------------------------------------------------------------------------------------|
| `setDialog(content)`         | The `.modal-dialog`. Accepts string, DOM node or `client.Response` (`html` or `json` with `output`).    |
| `setBody(content)`           | The `.modal-body`.                                                                                      |
| `setContent(content)`        | The whole `.modal-content`.                                                                             |
| `setHeader(content)`         | The header title.                                                                                       |
| `setFooter(content)`         | The footer.                                                                                             |
| `set(options)`               | Bulk update — `header`, `body`, `content`, `footer`, plus Bootstrap options like `backdrop`, `keyboard`. |

Modals extend [`ui.widget.Widget`](ui-js-components.md), so you can also drive configuration via `data-*` attributes on the markup.

## Loading remote content

### `load(url, options, originalEvent)`

GET request; the response markup must be the `modal-dialog`:

```js
modal.global.load(url).then(function (response) {
    // modal filled with response markup
}).catch(function (e) {
    module.log.error(e, true);
});
```

The view rendered server-side:

```php
<?php ModalDialog::begin(['header' => 'My Title']) ?>
    <div class="modal-body"><!-- ... --></div>
    <div class="modal-footer"><!-- ... --></div>
<?php ModalDialog::end() ?>
```

`load()` defaults to `dataType: 'html'`. For a JSON response, ship the markup as `output` — the modal pulls that field automatically.

### Declarative load via action

For simple click-to-load buttons, use the `ui.modal.load` action — no JS required:

```php
<!-- HTML response into the global modal -->
<button data-action-click="ui.modal.load" data-action-url="<?= $url ?>">
    Open
</button>

<!-- JSON response (output is plucked) into the global modal -->
<button data-action-click="ui.modal.load" data-action-url="<?= $jsonUrl ?>" data-type="json">
    Open
</button>

<!-- ...into a named modal -->
<button data-action-click="ui.modal.load" data-action-url="<?= $jsonUrl ?>" data-modal-id="myId">
    Open
</button>
```

When the button lives inside a modal, omit `data-modal-id` — the action targets the surrounding modal.

### POST

```js
modal.global.post(url, { data: { foo: 'value' } });
```

Or as a declarative action:

```html
<button data-action-click="ui.modal.post" data-action-url="<?= $url ?>">Post</button>
```

### Form submit

`ui.modal.submit` submits the surrounding form (via `client.submit`) and renders the response into the global modal:

```html
<button type="submit" data-action-click="ui.modal.submit">Save</button>
```

## Confirmation

`modal.confirm()` returns a promise that resolves to `true` (OK) or `false` (cancel):

```js
modal.confirm({
    header: 'Please confirm this action',
    body: 'Do you really want to execute this action?',
    confirmText: 'Yes',
    cancelText: 'No',
}).then(function (confirmed) {
    if (confirmed) {
        // ...
    }
});
```

All texts default to localised core values, so a bare `modal.confirm({ body: '…' })` is enough for most cases.

### Confirm on an action

For "click button → confirm → run action" without JS, attach the confirm attributes directly to the action element:

```php
<button data-action-click="someAction"
        data-action-confirm="Do you really want to execute this action?"
        data-action-confirm-header="Please confirm this action!"
        data-action-confirm-text="Yes"
        data-action-cancel-text="No">
    Do something
</button>
```

The action fires only after the user confirms.

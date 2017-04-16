Javascript UI Additions
=======

UI Additions are used to bind a specific behaviour to dom elements marked with a specific selector.
The `autosize` addition for example is bound to all textareas with an `.autosize` class attribute and enables an automatic resize of the textarea.

### Register Additions

New additions can be registered by calling the `ui.additions.register()` method as follows

```javascript
require('ui.additions').register('toggle', '.toggle', function($match) {
    $match.on('click', function() {
        $(this).toggle();
    });
});
```
In this example we added a `toggle` addition which will be applied to all nodes with a `.toggle` class selector.

> Note: the additions should be registered within your modules `init` function, in this case your addition will be automatically be applied within the initialization phase.

> Tip: You should only add new additions in case they are used regularly within your application, since each new addition will add an additional selector search to your initialization process.

###### Extend Additions:

The `register` method also accepts an optional `options` parameter after the handler function, which can be used to extend already registered additions.

The following options are available:

- `extend` can be used to extend an existing addition by either adding a handler or adding a selector, if this option is not set a registration call for an already registered addition will be ignored.
- `prepend` will prepend the new handler to the addition handler chain in case of an `extend` call if set to true

Our previous toggle addition can be extended as follows

```javascript
additions.register('toggle', '.toggle', function($match) {
    $match.on('dblclick', function() {
        // some other handler...
    });
}, {extend: true});

// or
additions.extend('toggle', '.toggle', function($match) { /* ... */ });
```

### Apply Additions

By default the document `body` will be parsed for additions within the humhub initialization phase. If you require to apply additions to nodes inserted after the initialization phase e.g. nodes loaded by ajax, you'll either have to call the `applyTo()` function on your new nodes as

```javascript
client.get(url).then(function(response) {
    $('#myContainer').html(response.output);
    additions.applyTo($('#myContainer'));
}).catch(function(e) {
    module.log.error(e,true);
});
```

or add a `MutationObserver` to your container as follows

```javascript
additions.observe($('#myContainer'));
```

> Info: You won't have to worry about the applying of additions when using the `modal` API to load content to your modals.

You can also just apply specific additions by using the apply options filter:

```javascript
    // only apply the toggle addition to #myContainer
    additions.applyTo($('#myContainer'), {
        filter: ['toggle']
    });

    // or respectively
    additions.observe($('#myContainer'), {
        filter: ['toggle']
    });
```

### Core Additions

 - *autosize* - `.autosize`: adds the autosize behaviour to textarea fields
 - *select2* - `[data-ui-select2]`: transforms a dropdown to a select2 dropdown
 - *tooltip* - `.tt`: adds an jQuery tooltip to the element on hover 
 - *markdown* - `[data-ui-markdown]`: parses the content of the given node for markdown syntax
 - *popover* - `.po`: adds the bootstrap popover behaviour to the given elements
 - *form_elements*, `:checkbox, :radio`: renders styled radiobuttons and checkboxes
 - *showMore*, `[data-ui-show-more]`: used for cutting long texts (e.g in stream entries)
 - *ui.widget*, `[data-ui-init]`: used for widgets with eager initialization

### Further Addition Features

 - `switchButtons` can be called to switch to buttons with a `bounceIn` effect
 
```javascript
// simple switch with bounceIn animation accepts either an selector or node or jQuery instance
additions.switchButtons('#hideMe', '#showMe');

// change animation
additions.switchButtons('#hideMe', '#showMe', {'animation': 'fadeIn'});

// remove the old button afterwards
additions.switchButtons('#hideMe', '#showMe', {'remove': true});
```

 - `highlight` adds an background highlight animation to a node

```javascript
// accepts either an string selector or node or jQuery instance
additions.highlight('#myTextNode');
```
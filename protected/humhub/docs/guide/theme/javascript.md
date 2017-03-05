# Javascript

Since the HumHub user interface is highly based on Javascript, some changes may require the alignment of a Javascript module.

You can intercept the installation of a module by means of the following events:

- `humhub:beforeInitModule`
- `humhub:afterInitModule`
- `humhub:modules:<module_id>:beforeInit`
- `humhub:modules:<module_id>:afterInit`
- `humhub:ready`

The following example shows how to overwrite an exported Javascript module function:

```
humhub.module('mytheme', function(module, require, $) {
    var event = require('event');
	
    event.on('humhub:ready', function() {
        var status = require('ui.status');
	    status.info = function() {
            // Some logic...
        }
    });
});
```



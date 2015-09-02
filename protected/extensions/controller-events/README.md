## controller-events

EControllerBehavior is a behavior that can handle controller events:

* beforeAction
* afterAction
* beforeRender
* afterRender

Your controller should extend EController.
You create a behavior by extending EControllerBehavior and implement one of above listed methods.
Inside a handler you have `$event` instance, which has following properties:
* in beforeRender: `$event->view`.
* in afterRender: `$event->view` and `$event->output`.
* in beforeAction: `$event->action`.
* in afterAction: `$event->action`.

Also, in beforeAction and beforeRender you can set `$event->isValid` property to allow or deny corresponding processing.

Also, it supports actions inside behaviors.

**Example**
```php
// main.php
	'import' => array('ext.controller-events.*'),
// CrudController.php
<?php
class CrudController extends EControllerBehavior {
	public function beforeAction(EControllerEvent $event) {
		if (rand(0, 1))
			$event->isValid = false;
	}
}
```

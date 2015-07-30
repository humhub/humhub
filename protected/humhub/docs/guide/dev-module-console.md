Console
=======

To add a custom controller to the console application, you need to catch the [[humhub\components\console\Application::EVENT_ON_INIT]].


### Example: Event 

```php
<?php

use humhub\components\console\Application;

return [
    'id' => 'translation',
    'class' => 'humhub\modules\translation\Module',
    'namespace' => 'humhub\modules\translation',
    'events' => array(
	    //...
        array('class' => Application::className(), 'event' => Application::EVENT_ON_INIT, 'callback' => array('humhub\modules\translation\Module', 'onConsoleApplicationInit')),
        //...
    ),
];
?>
```

### Example: Callback

```php
public static function onConsoleApplicationInit($event) {
    $application = $event->sender;
    $application->controllerMap['translation'] = commands\TranslationController::className();
}

```
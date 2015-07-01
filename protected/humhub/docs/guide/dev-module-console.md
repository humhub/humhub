Console
=======

To add a custom controller to the console application, you need to catch the [[humhub\components\console\Application::EVENT_ON_INIT]].


### Example: Event 

```php
<?php

use humhub\core\search\Events;
use humhub\components\console\Application;

Yii::$app->moduleManager->register(array(
    'isCoreModule' => true,
    'id' => 'search',
    'class' => \humhub\core\search\Module::className(),
    'events' => array(

        array('class' => Application::className(), 'event' => Application::EVENT_ON_INIT, 'callback' => array(Events::className(), 'onConsoleApplicationInit')),

    ),
));
?>
```

### Example: Callback

```php
public static function onConsoleApplicationInit($event) {
    $application = $event->sender;
    $application->controllerMap['search'] = commands\SearchController::className();
}

```
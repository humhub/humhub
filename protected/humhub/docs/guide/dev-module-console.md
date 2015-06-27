Console
=======

You can also add own console controller by events.

## Autostart Example

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

## Callback Example

```php
public static function onConsoleApplicationInit($event) {
    $application = $event->sender;
    $application->controllerMap['search'] = commands\SearchController::className();
}

```
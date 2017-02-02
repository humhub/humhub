Console Application
=====================

## Add controller to the console application

To add a custom controller to the console application, you need to catch the [[humhub\components\console\Application::EVENT_ON_INIT]].


Example event:

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

Example callback:

```php
public static function onConsoleApplicationInit($event) {
    $application = $event->sender;
    $application->controllerMap['translation'] = commands\TranslationController::className();
}

```

## Integrity Checker

The integrity checker is a command which validates and if necessary repairs the application database.

If you want to add own checking methods for your module to it, you can intercept the [[humhub\controllers\IntegrityController::EVENT_ON_RUN]] event.

Example callback implementation:

```php
public static function onIntegrityCheck($event)
{
    $integrityController = $event->sender;
    $integrityController->showTestHeadline("Polls Module - Answers (" . PollAnswer::find()->count() . " entries)");

    foreach (PollAnswer::find()->joinWith('poll')->all() as $answer) {
        if ($answer->poll === null) {
            if ($integrityController->showFix("Deleting poll answer id " . $answer->id . " without existing poll!")) {
                $answer->delete();
            }
        }
    }
}
```
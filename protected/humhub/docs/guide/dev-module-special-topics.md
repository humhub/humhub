Special Topics
==============

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


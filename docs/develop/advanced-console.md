# Console

HumHub exposes the same Yii console runner the framework provides — `php yii <controller>/<action>` from the `protected/` directory. Modules can ship their own controllers and tap into core console events (integrity check, cron).

## Adding a console controller

The preferred way (HumHub 1.7+) is the `consoleControllerMap` entry in your module's `config.php`. Place the controller class under `commands/`:

```php
// example/config.php
return [
    'id' => 'example',
    'class' => 'johndoe\example\Module',
    'namespace' => 'johndoe\example',
    'consoleControllerMap' => [
        'example' => 'johndoe\example\commands\ExampleController',
    ],
];
```

After enabling the module, `php yii example/<action>` is available.

For more complex setups — e.g. registering different controllers based on conditions — you can still listen for `humhub\components\console\Application::EVENT_ON_INIT` and populate `controllerMap` programmatically:

```php
use humhub\components\console\Application;

return [
    // ...
    'events' => [
        [
            'class' => Application::class,
            'event' => Application::EVENT_ON_INIT,
            'callback' => [Events::class, 'onConsoleApplicationInit'],
        ],
    ],
];
```

```php
public static function onConsoleApplicationInit($event)
{
    $application = $event->sender;
    $application->controllerMap['example'] = \johndoe\example\commands\ExampleController::class;
}
```

## Integrity checker hook

`php yii integrity/run` validates and (interactively) repairs application state. Modules with their own invariants should hook into it via `humhub\commands\IntegrityController::EVENT_ON_RUN`:

```php
public static function onIntegrityCheck($event)
{
    $controller = $event->sender;
    $controller->showTestHeadline('Polls module — Answers (' . PollAnswer::find()->count() . ' entries)');

    foreach (PollAnswer::find()->joinWith('poll')->all() as $answer) {
        if ($answer->poll === null) {
            if ($controller->showFix('Deleting poll answer id ' . $answer->id . ' without existing poll')) {
                $answer->delete();
            }
        }
    }
}
```

`showFix()` returns `true` when the operator confirms the fix interactively (or always in non-interactive mode). The pattern is: detect → describe → conditionally apply.

## Cron events

Core fires per-frequency cron events on `humhub\commands\CronController`:

- `CronController::EVENT_ON_HOURLY_RUN`
- `CronController::EVENT_ON_DAILY_RUN`

Subscribe in `config.php` for tasks that should run on the cron schedule:

```php
use humhub\commands\CronController;

'events' => [
    [
        'class' => CronController::class,
        'event' => CronController::EVENT_ON_HOURLY_RUN,
        'callback' => [Events::class, 'onHourlyRun'],
    ],
],
```

The cron is configured by the operator — see [admin → cron jobs](https://docs.humhub.org/docs/admin/cron-jobs).

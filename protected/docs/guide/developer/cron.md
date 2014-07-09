CronJobs
========

If your module needs to execute regular (hourly or daily) tasks, it can also intercept cron events.


## Example

__autostart.php__
```php
    //...
    'events' => array(
        // For hourly execution: onHourlyRun
        array('class' => 'ZCronRunner', 'event' => 'onDailyRun', 'callback' => array('ExampleModule', 'onCronDailyRun')),
    ),
    //...
```

__ExampleModule.php__
```php
    public static function onCronDailyRun($event) {
        // Do something daily
    }
```


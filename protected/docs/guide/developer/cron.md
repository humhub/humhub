CronJobs
========

If your module needs to execute regular (hourly or daily) tasks, it can intercept to the Zamm Cronjob System.


For a daily task:

         Yii::app()->interceptor->attachEventHandler('ZCronRunner', 'onDailyRun', array('MyModule', 'doSomethingDaily'));

For an hourly task:

         Yii::app()->interceptor->attachEventHandler('ZCronRunner', 'onHourlyRun', array('MyModule', 'doSomethingHourly'));
 
``Note:`` The hourly task may run more frequent!

If you need other times you may need to implement a distinct cli command runner (console.md) and your own cron job.



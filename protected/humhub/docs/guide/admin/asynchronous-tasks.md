Asynchronous Task Processing
============================

Introduction
------------

To provide a fast and responsive user experience, extensive processes are handled by background processes instead being directly executed on request. 

Some examples for such background processes are:

- Notifications (informing the users by e-mails or mobile push notifications) 
- Search index processing
- File indexing


Workers / Job Processing
------------------------

### Cronjob (Default)

You can start workers using cron by executing the queue/run command. It works as long as the queues contain jobs.

CronTab Example:

```
* * * * * /usr/bin/php <INSERT HUMHUB PATH HERE>/yii queue/run
```

In this case the cron will start the command every minute and execute scheduled tasks.


### Daemon 

You can start a worker deamon using following command:

```
cd protected
php yii queue/listen
```

***Using Supervisor (recommended)***

Supervisor is a process monitoring tool for Linux. It automatically starts, monitors and restarts your workers if they crash. 

Example configuration (e.g. /etc/supervisor/conf.d/humhub.conf):

```conf
[program:humhub-workers]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php <INSERT HUMHUB PATH HERE>/protected/yii queue/listen --verbose=1 --color=0
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=<INSERT HUMHUB PATH HERE>/protected/runtime/logs/yii-queue-worker.log
```




Queue Driver
------------

### MySQL Database Driver (Default)

If you don't have Redis or any other supported queuing software (RabbitMQ, Beanstalk or Gearman) running, this is the recommended driver.
To enable this driver you need to add following block to your local configuration file (protected/config/common.php):

```
    // ...
    'components' => [
        // ...

        'queue' => [
            'class' => 'humhub\components\queue\driver\MySQL',
        ],
        
        // ...
    ],
    // ...

```
### Redis 

If you're already using Redis (e.g. for caching or push) we recommend this queue driver.
Please make sure you already configured Redis as described here: [Redis Configuration](redis.md).

To enable this driver you need to add following block to your local configuration file (protected/config/common.php):

```
    // ...
    'components' => [
        // ...

        'queue' => [
            'class' => 'humhub\components\queue\driver\Redis',
        ],
        
        // ...
    ],
    // ...

```


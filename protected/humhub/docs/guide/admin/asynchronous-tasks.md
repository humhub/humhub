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
Replace the user `www-data` with the user of your http server if needed.

***Using systemd***

If systemd is installed on the system, a systemd timer could be an alternative to a cronjob.

This approach requires two files: `humhub.service` and `humhub.timer`. Create these two files in `/etc/systemd/system/`.

`humhub.service` should look like this:

```
[Unit]
Description=HumHub Queue Worker

[Service]
User=www-data
ExecStart=/usr/bin/php <INSERT HUMHUB PATH HERE>/protected/yii queue/listen --verbose=1 --color=0

[Install]
WantedBy=basic.target
```

Replace the user `www-data` with the user of your http server if needed.

`humhub.timer` should look like this:

```
[Unit]
Description=Run HumHub Queue Workers every minute

[Timer]
OnBootSec=1min
OnUnitActiveSec=1min
Unit=humhub.service

[Install]
WantedBy=timers.target
```

The important parts in the timer-unit are `OnBootSec` and `OnUnitActiveSec`.
`OnBootSec` will start the timer 1 minute after boot, otherwise you would have to start it manually after every boot.
`OnUnitActiveSec` will set a 1 minute timer after the service-unit was last activated.

Now all that is left is to start and enable the timer by running these commands:

```
systemctl start humhub.timer
systemctl enable humhub.timer
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
            'class' => 'humhub\modules\queue\driver\MySQL',
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
            'class' => 'humhub\modules\queue\driver\Redis',
        ],
        
        // ...
    ],
    // ...

```

### Sync and Instant Queue

The [[humhub\modules\queue\driver\Sync]] and [[humhub\modules\queue\driver\Instant]] queues are used in test and development environments without cron jobs.

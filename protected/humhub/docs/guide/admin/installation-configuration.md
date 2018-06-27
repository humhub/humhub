Configuration
=============

> NOTE: Before going to production, see also the [Security Chapter](security.md)


E-Mails
-------

Depending on your environment you are using, you may want to specify a local or remote SMTP Server.
You can change the mail-server settings under `Administration -> Mailing -> Server Settings`.

By default, the PHP Mail Transport is used. <http://php.net/manual/en/mail.setup.php>



CronJobs and Job Processing
---------------------------

- Execute scheduled tasks (**new**):
> /usr/bin/php /path/to/humhub queue/run
 
You only require this cron jobs if there is no other job worker configured. See [Asynchronous Tasks](asynchronous-tasks.md) for more details.


- Daily cron jobs (e.g. daily summaries, search index optimization)
> /usr/bin/php /path/to/humhub cron/daily


- Hourly cron jobs (e.g. update e-mails)
> /usr/bin/php /path/to/humhub cron/hourly


**Example CronTab configuration:**

```
* * * * *  /usr/bin/php /path/to/humhub/protected/yii queue/run >/dev/null 2>&1
30 * * * * /usr/bin/php /path/to/humhub/protected/yii cron/hourly >/dev/null 2>&1
0 18 * * * /usr/bin/php /path/to/humhub/protected/yii cron/daily >/dev/null 2>&1
```

> Note: For more help refer to [here](cron-jobs.md)!


Url Rewriting (Optional)
------------------------

Rename **.htaccess.dist ** to **.htaccess**
Modify the local configuration (protected/config/common.php):

```php
<?php

return [
    'components' => [
        'urlManager' => [
            'showScriptName' => false,
            'enablePrettyUrl' => true,
        ],
    ]
];

```  

Asynchronous Tasks
------------------

Please see the chapter [Asynchronous Tasks](asynchronous-tasks.md) for more details about queuing and job processing options.


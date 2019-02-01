Base Configuration
=============

This guide describes the base configurations required to operate a HumHub environment.

> NOTE: Before going to production, see also the [Security Chapter](security.md)


E-Mails
-------

Depending on your hosting environment, you may want to specify a local or remote SMTP Server.
The mail-server settings can be configured under `Administration -> Mailing -> Server Settings`.

By default, the [PHP Mail Transport](http://php.net/manual/en/mail.setup.php) is used.

Perhaps, you should consider a dedicated SMTP service, like SendGrid, Postmark, Amazon SES or Mailgun.


CronJobs and Asynchronous Job Processing (v1.3+)
---------------------------

**CronJobs** are used to execute scheduled tasks as for example sending _summary emails_ or _search index optimization_
and they need to be configured in your server environment.

The scheduled job-runner can be executed manually as follows:

```
> /usr/bin/php /path/to/humhub/protected/yii cron/run
```

**Asynchronous Jobs** are used to execute potentially long running tasks in the background and can either be:

- run by an extra CronJob (see below),
- or by a job-runner alternative.

> Please see [Asynchronous Tasks](asynchronous-tasks.md) for more details about queuing and job processing options.

The asynchronous job-runner can also be executed manually as follows:

```
> /usr/bin/php /path/to/humhub/protected/yii queue/run
```

> Note: If you're on a **shared hosting environment**, you may need to add the `--isolate=0` option to the `queue/run`. e.g. `/usr/bin/php /path/to/humhub/protected/yii queue/run --isolate=0`

If your environment provides a command-line specific build of `php`, often called `php-cli`, you may want to use that instead of `php` as it will have a cleaner output. On some hosts, the `php` command might be setup to discard the command-line parameters to console, in which case you must use `php-cli` to make the cron jobs work.

To assist in troubleshooting cron issues, you can pipe the output of each cron job to a specific file by adding something like `>/path/to/file.log 2>&1` at the end of the cron job instead of `>/dev/null 2>&1`. Then you can look at the contents of the file to see what was printed. If an error is occurring when running the cron job, you will see it there, otherwise the file will be empty or have some stats. The modification time of the file informs you of the last time the cron job ran. You can thus use this to figure out whether or not the cron job is running successfully and on schedule.

**Example CronTab configuration:**

These Cronjobs can be run together if you're **not** using any other job-runner _(like Supervisor or Systemd)_:

```
* * * * * /usr/bin/php /path/to/humhub/protected/yii queue/run >/dev/null 2>&1
* * * * * /usr/bin/php /path/to/humhub/protected/yii cron/run >/dev/null 2>&1
```

> Warning: These two cron jobs are only **both required** if there is no other worker configured. See [Asynchronous Tasks](asynchronous-tasks.md) for more details. Please also note that additional job-workers can be configured **only in dedicated environments, not shared hostings**.

In case you've configured a job-worker _(like Supervisor or Systemd)_, only the main Cronjob should be running paralel to the job-runner, so:

```
* * * * * /usr/bin/php /path/to/humhub/protected/yii cron/run >/dev/null 2>&1
```

> Note: Please see the [Cron Job Section](cron-jobs.md) for more information about the configuration of cron jobs.


URL Rewriting (Optional)
------------------------

URL rewriting can be used in order to use pretty urls. When enabled your HumHub installation will make use of urls like
`http://localhost/humhub/directory/directory` instead of `http://localhost/humhub/index.php?r=directory%2Fdirectory`.

Rename the `.htaccess.dist` file residing in your HumHub root folder to `.htaccess` and modify the local configuration located at `protected/config/common.php` as follow:

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

Further Configuration Options
------------------------

Please see the [Custom Configuration Section](advanced-configuration.md) for further information about other configuration
possibilities.

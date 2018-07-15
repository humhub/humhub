Base Configuration
=============

This guide describes the base configurations required to operate a HumHub environment.

> NOTE: Before going to production, see also the [Security Chapter](security.md)

E-Mails
-------

Depending on your environment you are using, you may want to specify a local or remote SMTP Server.
The mail-server settings can be configured under `Administration -> Mailing -> Server Settings`.

By default, the [PHP Mail Transport](http://php.net/manual/en/mail.setup.php) is used.


CronJobs and Asynchronous Job Processing (v1.3+)
---------------------------

**CronJobs** are used to execute scheduled tasks as for example sending _summary emails_ or _search index optimization_
and need to be configured in your server environment.

The scheduled job runner can be executed manually as follows:

```
> /usr/bin/php /path/to/humhub/protected/yii cron/run
```

**Asynchronous Jobs** are used to execute potentially long running tasks in the background and can either be run by
an extra CronJob or by other job-runner alternatives. Please see [Asynchronous Tasks](asynchronous-tasks.md) for more 
details about queuing and job processing options.

The asynchronous job-runner can be executed manually as follows:

```
> /usr/bin/php /path/to/humhub/protected/yii queue/run
```

**Example CronTab configuration:**

```
* * * * *  /usr/bin/php /path/to/humhub/protected/yii queue/run >/dev/null 2>&1
* * * * *  /usr/bin/php /path/to/humhub/protected/yii cron/run >/dev/null 2>&1
```

> Warning: You only require this cron jobs if there is no other job worker configured. See [Asynchronous Tasks](asynchronous-tasks.md) for more details.

> Note: Please see the [Cron Job Section](cron-jobs.md) for more information about the configuration of cron jobs.


URL Rewriting (Optional)
------------------------

URL rewriting can be used in order to use pretty urls. When enabled your HumHub installation will make use of urls like
`http://localhost/humhub/directory/directory` instead of `http://localhost/humhub/index.php?r=directory%2Fdirectory`.

Rename the `.htaccess.dist` file residing in your HumHub root folder to `.htaccess`
and modify  the local configuration `protected/config/common.php`:

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
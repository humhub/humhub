Configuration
=============

> NOTE: Before going to production, see also the [Security Chapter](security.md)

Basic Settings
-------
Once installed, you should have a look at the basic application settings under `Administration/Settings/Basic`
for settings as:

 - Base Url
 - Default language
 - Time zone
 - etc.

Within this view you can also select **default spaces**, which will be automatically assigned to new members.

Advanced Settings
-------
You may also check the advanced settings under `Administration/Settings/Advanced` and Notification settings to set
some default values for your user.


E-Mails
-------

Depending on your environment you are using, you may want to specify a local or remote SMTP Server.
You can change the mail-server settings under `Administration -> Mailing -> Server Settings`.

By default the PHP Mail Transport is used. <http://php.net/manual/en/mail.setup.php>


CronJobs
--------

 - Daily cron command: `> yii cron/daily`
 - Hourly cron command: `> yii cron/hourly`

Example Tab:

```
30 * * * * /path/to/humhub/protected/yii cron/hourly >/dev/null 2>&1
00 18 * * * /path/to/humhub/protected/yii cron/daily >/dev/null 2>&1
```


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


Job Scheduling
--------------

TBD
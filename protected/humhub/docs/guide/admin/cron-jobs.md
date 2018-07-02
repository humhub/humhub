Cron Job Setup
=======

The following guides are meant to help you with your Cron Job setup, since those settings are highly dependent on your actual environment we can't assure those setting will work for your.

> Note: Make sure to use the right [php cli executable](http://php.net/manual/en/features.commandline.introduction.php) for your jobs!

### CloudLinux (CentOS) 6
The following is a default setup for CloudLinux (CentOS) 6 and may not work for all users.

```
/usr/local/bin/php /home/USERNAME/public_html/WEB-DIRECTORY/protected/yii cron/run >/dev/null 2>&1

* * * * *

/usr/local/bin/php /home/USERNAME/public_html/WEB-DIRECTORY/protected/yii queue/run >/dev/null 2>&1

* * * * *
```

### cPanel Hosted Server
The following is a default setup for cPanel Hosted Server and may not work for all users.

```
/usr/local/bin/php /home/USERNAME/public_html/WEB-DIRECTORY/protected/yii cron/run >/dev/null 2>&1

* * * * *

/usr/local/bin/php /home/USERNAME/public_html/WEB-DIRECTORY/protected/yii queue/run >/dev/null 2>&1

* * * * *
```

### IIS Windows Server
Using [Schtasks](https://technet.microsoft.com/en-us/library/cc725744.aspx) would be recommended over many other options for Windows 2012 and Windows 8 users.

`Example TBA`

### Plesk
Refer to this [post](https://stackoverflow.com/questions/16700749/setting-up-cron-task-in-plesk-11)

![](http://i.imgur.com/TbWEsjC.png)

### OVH
[Follow this link](https://www.ovh.com/us/g1990.hosting_automated_taskscron)!

Create the following files then follow the above link.

**cronh.php**

`<?php $humhubh = '/usr/local/php5.6/bin/php '.__DIR__.'/protected/yii cron/run '; exec($humhubh); ?>`

**crond.php**

`<?php $humhubd = '/usr/local/php5.6/bin/php '.__DIR__.'/protected/yii queue/run '; exec($humhubd); ?>`

### Debian
Please read up on this [article](https://debian-administration.org/article/56/Command_scheduling_with_cron).

`Example TBA`

### Ubuntu
Please read up on this [how-to guide](https://help.ubuntu.com/community/CronHowto).

`Example TBA`

### Other Server(s)
`TBA`

### *IMPORTANT NOTICE*
*This guide is subject to changes and all information provided are for example use, it is best to speak with your service providers about how best to setup your Cron Jobs with their services.*

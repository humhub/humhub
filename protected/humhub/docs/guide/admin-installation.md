Installation
============

1. Grab the source and put them somewhere into htdocs
   TBD

2. Create an empty mysql database (utf8) e.g.
  ```sql
  CREATE DATABASE `humhub` CHARACTER SET utf8 COLLATE utf8_general_ci;
  GRANT ALL ON `humhub`.* TO `humhub_dbuser`@localhost IDENTIFIED BY 'password_changeme';
  FLUSH PRIVILEGES;  
  ```
3. Make the following directories/files writable by the webserver
    - /assets
    - /protected/config/
    - /protected/modules
    - /protected/runtime
    - /uploads/*

4. Make the following files executable
    - /protected/yii
    - /protected/yii.bat

4. Open installation folder in browser (e.g. http://localhost/humhub)

## Fine Tuning

### E-Mail Configuration

Depending on your environment which you are using you may want specify a local or remote SMTP Server.
You can change this settings at `Administration -> Mailing -> Server Settings`.

By default PHP Mail Transport is used. <http://php.net/manual/en/mail.setup.php>


### Enable Url Rewriting (Optional)

1. Rename **.htaccess.dist ** to **.htaccess**

2. Add to local configuration /protected/config/common.php

```php

'components' => [
    'urlManager' => [
        'showScriptName' => false,
        'enablePrettyUrl' => true,
    ],
]

```  

### Enable Cron Jobs

- Make sure the file protected/yiic is executable. (e.g. chmod +x protected/yiic)
- Add following lines to your crontab:

        30 * * * * /path/to/humhub/protected/yiic cron hourly >/dev/null 2>&1
        00 18 * * * /path/to/humhub/protected/yiic cron daily >/dev/null 2>&1


### Production Mode

#### Check Directory Protection

Make sure following directories are not accessible throu webserver.
(These folders are protected by default with ".htaccess")
- protected
- uploads/file

#### Disable Errors / Debugging

index.php in root 
     
```
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
```
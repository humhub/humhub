Installation
============

1. Grab the source and put them somewhere into htdocs, either
  - `git clone https://github.com/humhub/humhub.git` (__recommended__, for this you need git, obviously)
  - or download <https://github.com/humhub/humhub/archive/master.zip>
2. Create an empty mysql database (utf8) e.g.
  ```sql
  CREATE DATABASE `humhub` CHARACTER SET utf8 COLLATE utf8_general_ci;
  GRANT ALL ON `humhub`.* TO `humhub_dbuser`@localhost IDENTIFIED BY 'password_changeme';
  FLUSH PRIVILEGES;
  
  ```
3. Make the following directories/files writable by the webserver
    - /assets
    - /protected/config/local
    - /protected/modules
    - /protected/runtime
    - /uploads/*

4. Make the following files executable
    - /protected/yiic
    - /protected/yiic.bat
    - /protected/yiic.php

4. Open installation folder in browser (e.g. http://localhost/humhub)

E-Mail Configuration
--------------------

Depending on your environment which you are using you may want specify a local or remote SMTP Server.
You can change this settings at `Administration -> Mailing -> Server Settings`.

By default PHP Mail Transport is used. <http://php.net/manual/en/mail.setup.php>


Enable Url Rewriting (Optional)
-------------------------------


1. Make sure you installed & configured HumHub before and your installation is working.
2. Copy file .htaccess.dist to .htaccess
3. You may need to modify the default .htaccess file on some hosting environments. (See inline documentation)
4. Add urlManager lines in protected/config/local/_settings.php (Backup?)

        <?php return array (
          'components' => 
          array (

            [...]

            'urlManager' => array(
                'urlFormat' => 'path',
                'showScriptName' => false,
            ),

            [...]
        ); ?>
        

Enable Cron Jobs
----------------

- Make sure the file protected/yiic is executable. (e.g. chmod +x protected/yiic)
- Add following lines to your crontab:

        30 * * * * /path/to/humhub/protected/yiic cron hourly >/dev/null 2>&1
        00 18 * * * /path/to/humhub/protected/yiic cron daily >/dev/null 2>&1


Security/Production Mode
------------------------

1. Make sure following directories are not accessible throu webserver.
(These folders are protected by default with ".htaccess")

        - protected
        - uploads/file

2. Disable Errors / Debugging Open index.php in application root folder and disable debugging.
    
        [...]

        // Disable these 3 lines when in production mode
        //defined('YII_DEBUG') or define('YII_DEBUG', true);
        //defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 5);
        //ini_set('error_reporting', E_ALL);

        [...]

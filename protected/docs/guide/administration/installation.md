Installation
============

1. Create a mysql database e.g. humhub
2. Extract install package into a web/htdocs folder 
3. Make following directories/files writable by webserver
    - /assets
    - /protected/runtime
    - /protected/config/_settings.php
    - /uploads
4. Open that folder (e.g. http://localhost/humhub)
5. Installer will start


Enable Url Rewriting
--------------------

1. Rename file .htaccess.dist to .htaccess
2. Add urlManager lines in protected/config/_settings.php

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

Add following lines to your crontab:

        30 * * * * /path/to/humhub/protected/yiic cron hourly >/dev/null 2>&1
        00 18 * * * /path/to/humhub/protected/yiic cron daily >/dev/null 2>&1


Protected Directories
---------------------

Make sure following directories are not accessible throu webserver.
(These folders are protected by default per ".htaccess")

        - protected
        - uploads/file


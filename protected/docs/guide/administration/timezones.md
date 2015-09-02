Timezones
=========

HumHub uses by default the timezone of your PHP/MySQL installation. 

You can change the application timezone by editing the configuration at ``protected/config/local/_settings.php``.

```
<?php

return array(
    'timeZone' => 'Asia/Calcutta',
    ...
);

?>

```

To modify the database timezone you can add following parts to your database configuration.

```
<?php

return array(
    
    [...]

    'components' =>
    array(
        'db' =>
        array(
            'connectionString' => 'mysql:host=localhost;dbname=humhub',
            'username' => 'root',
            'password' => 'yourPassword',
            
            // Change Database Timezone:
            'initSQLs' => array("SET time_zone = '+4:30'"),
        ),
       
        ...
    );
);

?>

```
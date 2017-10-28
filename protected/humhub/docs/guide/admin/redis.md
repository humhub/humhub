Redis
=====

We recommend installing an additional Redis server which can act as a caching and job queuing service for HumHub.
In upcoming versions Redis will be required in order to enable push communication to the browser.


Basic Configuration
------------------

To enable Redis, you have to add following block to your local configuration file (protected/config/common.php):

```
    // ...
    'components' => [
        // ...

        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        
        // ...
    ],
    // ...

```

Caching 
-------

Once Redis is configured, you can also select it as a caching service: Administration -> Settings -> Advanced -> Caching.


Queuing of Asychronous Tasks
-------------------------

See [Asychronous Tasks](asychornous-tasks.md) for further information.



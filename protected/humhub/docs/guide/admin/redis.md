Redis
=====

We recommend installing an additional Redis server which can act as a caching, push service and job queuing service for HumHub.


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
----------------------------

See [Asychronous Tasks](asychornous-tasks.md) for further information.


Push Updates
------------

See [Push Updates](push-updates.md) for further information.



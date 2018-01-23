Push Updates / Push Service
===========================

The PushService directly sends updates (e.g. new notifications) to the users using WebSockets or Long Polling techniques.


Prerequisites
-------------

The PushService requires following additional installed software:

- NodeJS 
- Redis


PushService Installation
------------------------

You can install the HumHub PushService as NPM Package by entering following command:

```
npm install humhub-pushservice
```

Once the installation is finished, you need to create a configuration file:

```
cp config.json.dist config.json
```

Modify the config.json file and adjust the available settings.


Now you can start the PushService using following command:

```
node pushService.js
```


HumHub - Configuration
----------------------

Once the PushService NodeJS application is up and running you need to add following 
configuration options to the HumHub file (protected/config/common.php):

```
    // ...
    'components' => [
        // ...

        'live' => [
            'driver' => [
                'class' => \humhub\modules\live\driver\Push::class,
                'pushServiceUrl' => 'http://example.com:3000/',
                'jwtKey' => '---EnteraSuperSecretKeyToSignAuthorizationHere---'
            ]
        ],
        
        // ...
    ],
    // ...

```
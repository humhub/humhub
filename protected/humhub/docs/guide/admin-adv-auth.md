Authentication
==============

## Example: GitHub Authentication

- Obtain Application ID and Secret from GitHub
- Add following block to protected/config/common.php

```
    // ...
    'components' => [
        // ...
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'github' => [
                    'class' => 'yii\authclient\clients\GitHub',
                    'clientId' => '--->your-client-id<---',
                    'clientSecret' => '--->your-client-secret<---',
                    'normalizeUserAttributeMap' => [
                        'username' => 'login',
                        'firstname' => function ($attributes) {
                            list($f, $l) = mb_split(' ', $attributes['name'], 2);
                            return $f;
                        },
                        'lastname' => function ($attributes) {
                            list($f, $l) = mb_split(' ', $attributes['name'], 2);
                            return $l;
                        },
                    ],
                ],
            ],
        ],
        // ..
    ],
    // ...
```

## Example: Facebook

- Obtain Application ID and Secret from Facebook
- Add following block to protected/config/common.php

```
    // ...
    'components' => [
        // ...
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => '1662633133992750',
                    'clientSecret' => '3c786f625a26f7f3649bd5cc8d8c6a61',
                    'normalizeUserAttributeMap' => [
                        'username' => 'name',
                        'firstname' => function ($attributes) {
                            list($f, $l) = mb_split(' ', $attributes['name'], 2);
                            return $f;
                        },
                        'lastname' => function ($attributes) {
                            list($f, $l) = mb_split(' ', $attributes['name'], 2);
                            return $l;
                        },
                    ],                
                ],
            ],
        ],
        // ..
    ],
    // ...
```

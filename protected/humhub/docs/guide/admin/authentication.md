Authentication
==============

LDAP
----

You can enable authentication against LDAP (e.g. against Active Directory or OpenLDAP) at: `Administration -> Users -> Settings-> LDAP`.
The profile field attribute mapping can be defined at `Administration -> Users -> Profile -> Select profile field -> LDAP Attribute`.

### Date field synchronisation

If you're using custom date formats in our ldap backend, you can specify different formats
in the [configuration file](advanced-configuration.md).

```php
    'params' => [
        'ldap' => [
            'dateFields' => [
                'somedatefield' => 'Y.m.d'
            ],
        ],
    ],
```

Note: Make sure to use lower case in the field.



Facebook
--------

In order to use Facebook OAuth you must register your application at <https://developers.facebook.com/apps>.

Add the following block to your configuration (protected/config/common.php):

```php
return [
    // ...
    'components' => [
        // ...
        'authClientCollection' => [
            'clients' => [
                // ...
                'facebook' => [
                    'class' => 'humhub\modules\user\authclient\Facebook',
                    'clientId' => 'Your Facebook App ID here',
                    'clientSecret' => 'Your Facebook App Secret here',
                ],
            ],
        ],
        // ...
    ],
    // ...
];
```

Google
------

In order to use Google OAuth you must create a **project** at <https://console.developers.google.com/project>
and setup its credentials at <https://console.developers.google.com/project/[yourProjectId]/apiui/credential>.

In order to enable using scopes for retrieving user attributes, you have to enable Google+ API at
<https://console.developers.google.com/project/[yourProjectId]/apiui/api/plus>.

Add one of the following **authorization callback URLs** to your  googles **Credentials** configuration:
- http://<domain>/<path-to-humhub>/user/auth/external?authclient=google (With clean urls enabled)
- http://<domain>/<path-to-humhub>/index.php?r=user%2Fauth%2Fexternal&authclient=google (Without clean urls)

>Note: Replace **domain** and **path-to-humhub** in the mentioned redirect urls.

Add following block to your configuration (protected/config/common.php):

```php
return [
    // ...
    'components' => [
        // ...
        'authClientCollection' => [
            'clients' => [
                // ...
                'google' => [
                    'class' => 'humhub\modules\user\authclient\Google',
                    'clientId' => 'Your Client ID here',
                    'clientSecret' => 'Your Client Secret here',
                ],
            ],
        ],
        // ...
    ],
    // ...
];
```

GitHub
------

In order to use GitHub OAuth you must register your application at <https://github.com/settings/applications/new>.

Authorization callback URLs:
- http://domain/path-to-humhub/user/auth/external (With clean urls enabled)
- http://domain/path-to-humhub/index.php?r=user%2Fauth%2Fexternal (Without clean urls)

Add following block to your configuration (protected/config/common.php):

```php
return [
    // ...
    'components' => [
        // ...
        'authClientCollection' => [
            'clients' => [
                // ...
                'github' => [
                    'class' => 'humhub\modules\user\authclient\GitHub',
                    'clientId' => 'Your GitHub Client ID here',
                    'clientSecret' => 'Your GitHub Client Secret here',
                    // require read access to the users email
                    // https://developer.github.com/v3/oauth/#scopes
                    'scope' => 'user:email',
                ],
            ],
        ],
        // ...
    ],
    // ...
];
```


Microsoft Live
--------------

In order to use Microsoft Live OAuth you must register your application at <https://account.live.com/developers/applications>.

Also add a new Platform and allow following Redirect URI.

- https://domain/path-to-humhub/user/auth/external (With clean urls enabled)
- https://domain/path-to-humhub/index.php (Without clean urls)

Add following block to your configuration (protected/config/common.php):

```php
return [
    // ...
    'components' => [
        // ...
        'authClientCollection' => [
            'clients' => [
                // ...
                'live' => [
                    'class' => 'humhub\modules\user\authclient\Live',
                    'clientId' => 'Your Microsoft application ID here',
                    'clientSecret' => 'Your Microsoft application password here',
                ],
            ],
        ],
        // ...
    ],
    // ...
];
```

Other providers
---------------

Please see [Development - Authentication](dev-authentication.md) for more information
about additional authentication providers. 

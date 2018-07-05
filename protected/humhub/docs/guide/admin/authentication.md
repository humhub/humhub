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
In order to use Facebook OAuth you must register your application at <https://developers.facebook.com/apps> then follow the below instructions.

- In your app settings under **Basic** set your **Site URL**.
- Under **Settings > Advanced > Domain Manager** add any sub-domains used by the app.
- Under **Facebook Login > Client OAuth Settings > Valid OAuth Redirect URIs** place your `https://domain/path-to-humhub/user/auth/external?authclient=facebook` URL.
> https://domain/path-to-humhub/user/auth/external?authclient=facebook (With clean urls)

> http://domain/path-to-humhub/index.php?r=user%2Fauth%2Fexternal&authclient=facebook (Without clean urls)
- Make sure **Client OAuth Login** & **Web OAuth Login** are both enabled!
- Add the following block to your configuration (protected/config/common.php):

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

Twitter
------
In order to use Twitter OAuth you must register your application at <https://apps.twitter.com/>.

Add the following block to your configuration (protected/config/common.php):

```php
return [
    // ...
    'components' => [
        // ...
        'authClientCollection' => [
            'clients' => [
                // ...
                'twitter' => [
                'class' => 'yii\authclient\clients\Twitter',
                   'attributeParams' => [
                       'include_email' => 'true'
                   ],
                    'consumerKey' => 'Your Twitter Consumer key here',
                    'consumerSecret' => 'Your Twitter Consumer secret here',
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
and set up its credentials at <https://console.developers.google.com/project/[yourProjectId]/apiui/credential>.

In order to enable using scopes for retrieving user attributes, you should also enable Google+ API at
<https://console.developers.google.com/project/[yourProjectId]/apiui/api/plus>.

Authorization callback URLs:

Add one of the following **authorization callback URLs** to your  googles **Credentials** configuration:
- http://domain/path-to-humhub/user/auth/external?authclient=google (With clean urls enabled)
- http://domain/path-to-humhub/index.php?r=user%2Fauth%2Fexternal&authclient=google (Without clean urls)

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

Add the following block to your configuration (protected/config/common.php):

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

Also, add a new Platform and allow following Redirect URI.

- https://domain/path-to-humhub/user/auth/external (With clean urls enabled)
- https://domain/path-to-humhub/index.php (Without clean urls)

Add the following block to your configuration (protected/config/common.php):

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

LinkedIn
------
In order to use LinkedIn OAuth you must register your application at <https://www.linkedin.com/developer/apps/>.

Add the following block to your configuration (protected/config/common.php):

```php
return [
    // ...
    'components' => [
        // ...
        'authClientCollection' => [
            'clients' => [
                // ...
                'linkedin' => [
                    'class' => 'humhub\modules\user\authclient\LinkedIn',
                    'clientId' => 'Your LinkedIn Client ID here',
                    'clientSecret' => 'Your LinkedIn Client Secret here',
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

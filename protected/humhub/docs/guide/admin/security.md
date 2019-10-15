Security
========

This guide describes recommended configurations and practices in order to keep your network secure.

Enable Production Mode
--------------------------

By default HumHub is operating in _DEBUG_ mode, which besides others uses a different error handling and non combined
assets. Before opening your installation to the public you should enable the production mode first by commenting out the
following lines of the `index.php` file within your HumHub root directory:

```php
[...]
// comment out the following two lines when deployed to production
// defined('YII_DEBUG') or define('YII_DEBUG', true);
// defined('YII_ENV') or define('YII_ENV', 'dev');
[...]
```

> Note: In this example the lines are already commented out.

You should also delete the `index-test.php` file in your HumHub root directory if existing.

Protected Directories
---------------------

Please make sure you followed the directory permissions described in the [Installation Guide](installation.md#file-permissions)!

Limit User Access
-----------------

If you're running a private social network, make sure the user registration has been disabled or the approval system for new users has been enabled.

- Disable user registration: `Administration -> Users -> Settings -> Anonymous users can register`
- Enable user approvals: `Administration -> Users -> Settings -> Require group admin approval after registration`
- Make sure guest access is disabled: `Administration -> Users -> Settings -> Allow limited access for non-authenticated users (guests)`

Password Strength Configuration
-------------------------------

HumHub provides an option for adding of additional validation rules for user password during registration using regular expressions. 
Additional password validation rules can be configured, by changing applications parameters withing the **protected/config/common.php** configuration 

```php
return [
    'modules' => [
        'user' => [
            'passwordStrength' => [
                '/^(.*?[A-Z]){2,}.*$/' => 'Password has to contain two uppercase letters.',
                '/^.{8,}$/' => 'Password needs to be at least 8 characters long.',
            ]
        ]
    ]
];
```

Key should be a valid regular expression, and value - error message.
To localize error message you have to define a new message file with the following path pattern:

`protected/humhub/messages/<language>/custom.php`

Web Security Configuration
---------------------

HumHub 1.4 comes with a build in web security configuration used to set security headers and csp rules. The default security
configuration can be found at `protected/humhub/config/web.php`.

Since the default security settings are rather loose, you may want to align those settings to your own requirements. 
The strictest CSP settings for your installation highly depend on the used features as installed modules, configured oembed provider or
custom iframe pages etc. 

The following example demonstrates a stricter web security model:

**protected/config/web.php:**

```php
return [
    'modules' => [
        'web' => [
            'security' =>  [
                "headers" => [
                    "Strict-Transport-Security" => "max-age=31536000",
                    "X-XSS-Protection" => "1; mode=block",
                    "X-Content-Type-Options" => "nosniff",
                    "X-Frame-Options" => "deny",
                    "Referrer-Policy" => "no-referrer-when-downgrade",
                    "X-Permitted-Cross-Domain-Policies" => "master-only",
                ],
                "csp" => [
                    "nonce" => true,
                    "report-only" => false,
                    "report" => false,
                    "default-src" => [
                        "self" => true
                    ],
                    "img-src" => [
                        "data" => true
                        "allow" => [
                            "*"
                        ]
                    ],
                    "font-src" => [
                        "self" => true
                    ],
                    "style-src" => [
                        "self" => true,
                        "unsafe-inline" => true
                    ],
                    "object-src" => [],
                    "frame-src" => [
                        "self" => true
                    ],
                    "script-src" => [
                        "self" => true,
                        "unsafe-inline" => true,
                        "unsafe-eval" => false,
                        "report-sample" => true
                    ],
                    "upgrade-insecure-requests" => true
                ]
            ]
        ]
    ]
]
```

There are three main configuration section within your security settings as described in the following:

`headers`:

This part may contain security headers and values as for example:

- [Strict-Transport-Security](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security)
- [X-XSS-Protection](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection)
- [X-Content-Type-Options](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options)
- [X-Frame-Options](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options)
- [Referrer-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy)
- [X-Permitted-Cross-Domain-Policies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy)

If you want to add a `Content-Security-Policy` header in the `headers` section of your configuration, remove the `csp` section.

`csp`:

The csp section is used to configure the [Content-Security-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy)
which manages allowed resources as for example scripts, images and stylesheets. 

Please refer to the following links for more information about the CSP and the configuration format used in HumHub:

- [Content-Security-Policy (MDN web docs)](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy)
- [CSP Builder](https://github.com/paragonie/csp-builder#example)

> Note: the examples shown in the CSP Builder documentation use the JSON format while the HumHub configuration uses a PHP array format.

`csp-report-only`:

This section can be used to define a csp rule, which will only log violations to `Administration -> Information -> Logging`
rather than blocking the resources on the client. This can be used to test csp rules on your installation.

**CSP Reporting:**

As described above, the `csp-report-only` section of your web security configuration can be used to define csp rules
which are only used for debugging and testing and do not have any affect on the client. The `csp-report-only` can be used along
with the `csp` configuration.

It is also possible to set the `report` setting of your `csp` section to true, this will enable csp violation logging
while enforcing the csp rule.

**CSP Nonce:**

The csp also supports a [nonce](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src) 
settings for your `script-src`.  This can be enabled by setting `nonce => true` within your custom security configuration.
If enabled modern browsers will only execute scripts containing a generated nonce token.

> Note: Since this feature is rather new, some modules may do not support this feature.

> Note: Some settings as the nonce configuration, may not be supported by some modules. In case you notice modules not working
properly with your security configuration, please contact the module owner or refer to the module description. Also check the 
[Developer Javascript Guide](../developer/javascript.md) for assuring nonce support of your custom modules.

> Note: The security rules are cached, you may have to clear the cache in order to update the active rule configuration.

**CSP Guideline:**

This section assembles some guidelines and restrictions regarding custom CSP settings in HumHub.

- The HumHub core currently requires `img-src data:` for page icon and image upload `Administration -> Settings -> Appearance`
- When using the enterprise edition you should allow `https://www.humhub.org` for `frame-src`
- When noticing any issues with external modules, please inform the module owner.
- When developing custom modules, try to test against the strictest csp rules (see default acceptance test csp rules) and provide
information about csp restrictions in your module description.

Keep HumHub Up-To-Date 
---------------------------------------

As an admin you'll receive notifications about new HumHub releases. We strongly recommend to always update to the latest stable version if possible.
Check the [automatic](updating-automatic.md) or [manual](updating.md) update guide for more information about updating your HumHub installation.

Furthermore, you should regularly check the `Administration -> Modules -> Available Updates` section for module updates. 

We take security very seriously, and we're continuously improving the security features of HumHub. 

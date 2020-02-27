Advanced Configuration
======================

You can overwrite the default HumHub / Yii configuration in the folder `/protected/config`. 

File Overview
-------------

- **common.php**  - Configuration used in Console & Web Application
- **web.php** - Configuration used in Web Application only
- **console.log** - Configuration used in Console Application only
- **dynamic.php** - Dynamic generated configuration - do not edit manually!

Configuration file loading order
---------------------------------

**Web Application**

1. humhub/config/common.php
2. humhub/config/web.php
3. config/dynamic.php
4. **config/common.php**
5. **config/web.php**


**Console Application**

1. humhub/config/common.php
2. humhub/config/console.php
3. config/dynamic.php
4. **config/common.php**
5. **config/console.php**

> Note: Do not manipulate the `humhub/config/common.php`, `humhub/config/web.php`, `humhub/config/console.php`  or  `config/dynamic.php` directly. 

# Application Params

Some application behaviours can be configured, by changing application parameters within your `common.php`, `web.php` or `console.php`:

```
return [
    'params' => [
        'enablePjax' => false
    ]
];
```

The previous configuration will disable pjax support on your site.

Available params:

- `allowedLanguages` see the [Translations Section](translations.md)
- `enablePjax` used to disable/enable pjax support (default true)

# Module Configuration

Some modules may allow further configurations by overwriting fields of their `Module.php` class. 
Those configurations can be overwritten within your `common.php` file as follows:

```php
return [
    'modules' => [
        'directory' => [
            'guestAccess' => false 
        ]
    ]
]
```

# Statistics/Tracking

Your tracking code can be managed under `Administration -> Settings -> Advanced -> Statistics`.

In order to send the tracking code in case of pjax page loads as well as full page loads, you have to add the following to your statistics code by the example of google analytics:


```twig
<script nonce="{{ nonce }}">
    $(document).on('pjax:end', function() {
        ga('set', 'location', window.location.href);
        ga('send', 'pageview');
    });
</script>
```

or by using the old ga version:

```twig
<script nonce="{{ nonce }}">
    $(document).on('pjax:end', function() {
        if( window._gaq ) {
            _gaq.push(['_trackPageview', window.location.href]);
        }
    });
</script>
```

Please see [Single Page Application Tracking](https://developers.google.com/analytics/devguides/collection/analyticsjs/single-page-applications)
for more information about google analytics configuration in single page application environments.

> Note: Since HumHub 1.4 you should add the `nonce="{{ nonce }}` attribute to your script tag in order to be compatible with [csp nonces](security.md#security-configuration)

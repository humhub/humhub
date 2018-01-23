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

Thre previous configuration will disable pjax support on your site.

Available params:

- `allowedLanguages` see the [Translations Section](translations.md)
- `defaultPermissions` see [Permissions Section (TBD)]()
- `enablePjax` used to disable/enable pjax support (default true)

# Statistics/Tracking

Your tracking code can be managed under `Administration -> Settings -> Advanced -> Statistics`.

In order to send the tracking code in case of pjax page loads as well as full page loads, you have to add the following to your statistics code by the example of google analytics:


```javascript
$(document).on('pjax:end', function() {
    ga('set', 'location', window.location.href);
    ga('send', 'pageview');
});
```

or by using the old ga version:

```javascript
$(document).on('pjax:end', function() {
    if( window._gaq ) {
        _gaq.push(['_trackPageview', window.location.href]);
    }
});
```

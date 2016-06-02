Advanced Configuration
======================

You can overwrite the default HumHub / Yii configuration in the folder `/protected/config`. 

## File Overview

- **common.php**  - Configuration used in Console & Web Application
- **web.php** - Configuration used in Web Application only
- **console.log** - Configuration used in Console Application only
- **dynamic.php** - Dynamic generated configuration - do not edit manually!

## Loading Order

### Web Application

1. humhub/config/common.php
2. humhub/config/web.php
3. config/dynamic.php
4. **config/common.php**
5. **config/web.php**


### Console Application

1. humhub/config/common.php
2. humhub/config/console.php
3. config/dynamic.php
4. **config/common.php**
5. **config/console.php**

## Configurations

# Language


**Restrict Languages:**

All available languages are configured within the main HumHub confiuration **humhub/config/common.php**. 
It is possible to restrict the allowed languages of a HumHub instance by the following setting within
the web or common config:

```php
return [
    'params' => [
        'allowedLanguages' => ['de', 'fr']
    ]
];
```

This setting will only allow the selection of german and french.

**Overwrite Default Texts:**

To overwrite the default text for a language you have to define a new message file with the following path:

```
config/messages/<language>/<Module_ID>.<messagefile>.php
``` 

To overwrite the post placeholder for the german language, for example, you have to create the following file:

```
config/messages/de/PostModule.widgets_views_postForm.php
```

with the content:

```php
<?php
return array (
  'What\'s on your mind?' => 'Wie geht es dir heute?',
);
```

# Further available params

The following params are available to ch

 - **moduleAutoloadPaths** - Can be used to change the path of your modules folder

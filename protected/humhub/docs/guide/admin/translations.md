Translations
============

Limit available languages
-------------------------

The allowed languages of your project can be configured within the **humhub/config/common.php** configuration. 
It is possible to restrict the allowed languages of your HumHub installation by means of the following configuration:

```php
return [
    'params' => [
        'allowedLanguages' => ['de', 'fr']
    ]
];
```

Overwrite translation messages
------------------------------

To overwrite the default text for a language, you have to define a new message file with the following path pattern:

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


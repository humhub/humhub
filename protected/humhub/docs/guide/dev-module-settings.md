Settings Manager
================

The SettingsManager allows you to easily store key/value based configuration settings 
based on module components and also optionally bound to a contentcontainer (e.g. users or spaces).

If you need to categorize key names, use this syntax: category.subcategory.camelCaseKeyName

The SettingsManager component is automatically added all to humhub\components\Module classes.

Module settings
---------------

Get desired module / application instance:

```php
$module = Yii::$app;
// or
$module = Yii::$app->getModule('polls');
// or
$module = $controller->module;
```


Create or update existing setting in settings manager:

```php
$module->settings->set('key', $value);
```


Get value of setting manager:

```php
$value = $module->settings->get('key');
```


Delete setting:

```php
$module->settings->delete('key');
// or
$module->settings->set('key', null);
```


ContentContainer related settings
---------------------------------

If you want to store settings related to an user or space - use the ContentContainerSettingsManager:

```php
$module->settings->contentContainer($user)->get('key');
$module->settings->contentContainer($user)->set('key', $value);
$module->settings->contentContainer($user)->delete('key');
```


Shortcuts for currently logged in user settings:

```php
$module->settings->user()->get('key');
$module->settings->user()->set('key', $value);
$module->settings->user()->delete('key');
```

Shortcuts for current space settings:
Note: This is only available if current controller is instance of ContentContainerController.

```php
$module->settings->space()->get('key');
$module->settings->space()->set('key', $value);
$module->settings->space()->delete('key');
```

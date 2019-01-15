Settings and Configuration
================

By means of a [[humhub\components\SettingsManager]] you can easily store module level key/value settings optionally bound 
to a `ContentContainer` (e.g. users or spaces).

The SettingsManager component is automatically added all to humhub\components\Module classes.

> Note: In order to categorize your key names, use this syntax: `category.subcategory.camelCaseKeyName`

## Module settings

Get desired module:

```php
$module = Yii::$app->getModule('polls');
// or
$module = $controller->module;
```

**Create or update** existing setting in settings manager:

```php
$module->settings->set('key', $value);
```

Load setting with given **default** value:

```php
$value = $module->settings->get('key', $defaultValue);
```

Save a serialized setting:

```php
$value = $module->settings->setSerialized($key, [['complex' => 'value']])
```

> Note: `setSerialized` will use `Json::encode()` to serialize.

**Delete setting:**

```php
$module->settings->delete('key');
// or
$module->settings->set('key', null);
```

## ContentContainer related settings

If you want to store settings related to an user or space - use the `ContentContainerSettingsManager` as follows:

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

Sometimes you'll want to use a global setting as default. The following call will try to load a container specific setting,
and will fall back to a global setting with the same key. If no container specific nor a global setting was found the `$default`
value will be returned.

```php
$module->settings->user()->getInherit($settingKey, $default);
```



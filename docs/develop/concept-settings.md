# Settings

`SettingsManager` stores key/value pairs per module — optionally scoped to a content container (user or space). Every `humhub\components\Module` ships with a `$settings` property automatically.

For nested keys, use dotted notation: `category.subcategory.camelCaseKeyName`.

The data lives in the `setting` and `contentcontainer_setting` tables. Reads are cached, so they're cheap to call from hot paths after the first lookup.

## Module-scoped settings

```php
$module = Yii::$app->getModule('polls');
// or, from a controller:
$module = $controller->module;

$module->settings->set('key', $value);
$value = $module->settings->get('key');
$module->settings->delete('key');                 // delete
$module->settings->set('key', null);              // also deletes
```

`get()` returns `null` for missing keys; pass a default as the second argument:

```php
$value = $module->settings->get('key', 'default');
```

## Per-container settings

For settings that vary per space or per user, use `contentContainer()`:

```php
$module->settings->contentContainer($user)->get('key');
$module->settings->contentContainer($user)->set('key', $value);
$module->settings->contentContainer($user)->delete('key');
```

### Convenience accessors

For the current logged-in user:

```php
$module->settings->user()->get('key');
$module->settings->user()->set('key', $value);
$module->settings->user()->delete('key');
```

For the space of the current request (only inside a `ContentContainerController`):

```php
$module->settings->space()->get('key');
$module->settings->space()->set('key', $value);
$module->settings->space()->delete('key');
```

## Cleanup on module disable

The default `Module::disable()` clears all global settings and `ContentContainerModule::disableContentContainer()` clears all container settings belonging to your module — you don't need to enumerate keys yourself when uninstalling.

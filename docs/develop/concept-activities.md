# Activities

Activities record noteworthy things that happened in a content container — "X created a post", "Y joined the space", "Z liked a comment". Each activity is rendered into a stream entry and may also appear in the daily summary email.

The two related concepts:

- **Activity** — bound to a `ContentContainer`, not targeted at a specific user. Anyone with access to the container sees it. Compare with [notifications](concept-notifications.md), which target a specific user.
- **Originator** — the user the activity is *about* (e.g. the comment author).
- **Source** — an optional `Content` or `ContentAddon` the activity describes. When a source is set, the activity inherits its `visibility` and other content properties.

## Implementing an activity

Place activity classes under your module's `activities/` directory.

### 1. The activity class

```php
namespace johndoe\example\activities;

use humhub\modules\activity\components\BaseActivity;

class SomethingHappened extends BaseActivity
{
    public $viewName = 'somethingHappened';   // view file (without .php)
    public $moduleId = 'example';             // required
}
```

### 2. The view file

By default the view sits at `activities/views/<viewName>.php`. It receives `$originator`, the source record, and a few helpers:

```php
use yii\helpers\Html;

echo Yii::t('ExampleModule.activity', '{user} did something cool.', [
    '{user}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
]);
```

For a different mail rendering, ship a `activities/views/mail/<viewName>.php` with the same view name — `BaseActivity` picks it up automatically when generating the summary mail.

### 3. Firing the activity

```php
SomethingHappened::instance()
    ->from($user)        // originator
    ->about($this)       // source (Content or ContentAddon)
    ->create();
```

`create()` persists the activity, dispatches stream entries, and queues mail delivery if applicable. Activities are typically fired from the `afterSave()` hook of the source record — once per state transition, never on every save.

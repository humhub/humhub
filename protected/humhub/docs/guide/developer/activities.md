Activities
==========

Activity instances are created for special events in the context of a [[humhub\modules\content\models\ContentContainer|ContentContainer]] like the creation of content.

Contrary to notifications - activities are always bound to a [[humhub\modules\content\models\ContentContainer|ContentContainer]], and are not targeted for specific users.
Besides the relation to the `ContentContainer` - an activity can also be assigned with a Content or ContentAddon and automatically inherits some content properties such as `visibility`.

Similar to [notifications](notifications.md) activities can be related to an `originator` user and a `source` object. 
The activity will adopt some properties as the `visibility` in cas of `Content` or `ContentAddon` sources.

## Implement a Custom Activity

### Create Class & View

Your custom activites should reside in your modules `activities` directory.

Example activity:

```php
<?php

namespace johndoe\example\activities;

use humhub\core\activity\components\BaseActivity;

class SomethingHappend extends BaseActivity
{
    // View Name for activity
    public $viewName = "somethingHappend";

    // Module Id (required)
    public $moduleId = "example";
}
?>
```

By default activity views should be located inside a `activities/views` directory (e.g. `mymodule/activities/views/`).

Example view file **somethingHappend.php**:

```php
<?php

use yii\helpers\Html;

echo Yii::t('ExampleModule.views_notifications_newLike', "%someUser% did something cool.", array(
    '%someUser%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
));
?>
```

If you require a different view for mails, you have to  add a view with the same view name to the `activities/views/mail` directory.  

### Activity creation

```php
SomethingHappend::instance()->from($user)->about($this)->create();
```
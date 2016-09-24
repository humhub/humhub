Activities
==========

Activity instances are created for special events in the context of a [[humhub\modules\content\models\ContentContainer|ContentContainer]] like the creation of new content.

Contrary to notifications - activities are always bound to a [[humhub\modules\content\models\ContentContainer|ContentContainer]], so they are not especially linked against a user or a given set of them.
Besides the link to the [[humhub\modules\content\models\ContentContainer|ContentContainer]] - an activity can also be assigned with a Content or ContentAddon and automatically inherits some content attributes such as visiblity.

## Implement a Custom Activity

### Create Class & View

Create a folder ** activities ** in your module and a new class ** SomethingHappend ** 

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

By default activity views should be located inside a subfolder named ** views ** where your activity class is located (e.g. /modules/examples/activities/views/).

Example view file ** somethingHappend.php **:

```php
<?php

use yii\helpers\Html;

echo Yii::t('ExampleModule.views_notifications_newLike', "%someUser% did something cool.", array(
    '%someUser%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
));
?>
```

If you require a different view for mails, you have to create a  ** mail ** folder in your views directory.  

### Save an Activity

```php
$activity = new \johndoe\example\activities\NewLike();

// Link to a ContentContainer, Content or ContentAddon 
$activity->source = $this;

// User which trigged this Activity - in case of Content/ContentAddon the Creator will be automatically set.
$activity->originator = $user;

$activity->create();
```


### Delete

TBD
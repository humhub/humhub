Activities
==========

Contrary to Notifications - Activities are bound to a ContentContainer, so they are not especially linked against a user or  a given set of them.

Besides the link to the ContentContainer - an Activity can be also assigned to a Content or ContentAddon. So it will automatically inherits some Content Attributes such as Visiblity.

Note: Internally Activities will be handled as Content.

## Steps to create an Activity

### Create Class & View

Create a folder ** activities ** in your module and a new class ** SomethingHappend ** 

```php
<?php

namespace johndoe\example\activities;

use humhub\core\activity\components\BaseActivity;

/**
 * Notifies a user about something happend
 */
class SomethingHappend extends BaseActivity
{
	// View Name for activity
    public $viewName = "somethingHappend";

	// Module Id (required)
	public $moduleId = "example";

}

?>

```

By default activity views should be located inside a subfolder named ** views ** where your activity class is located. (e.g. /modules/examples/activities/views/)

Example view file ** somethingHappend.php **:

```php
<?php

use yii\helpers\Html;

echo Yii::t('ExampleModule.views_notifications_newLike', "%someUser% did something cool.", array(
    '%someUser%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
));
?>


```

If you require a diffrent view in mails. You can create a subfolder inside the subfolder called ** mail ** in your views directory.  


### Create it

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
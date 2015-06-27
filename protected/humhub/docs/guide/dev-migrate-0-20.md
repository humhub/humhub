# Migration Guide to HumHub 0.20

## ToDo
- Theming
- Check Activity Situations
- Some Core Modules
- Check Update Progress
- Activity/Notification - create own classes/views
- Check Timezone
- Emailing
- CronJobs
- Tests
- HSetting -> Add Caching & Co -> E-Mailing (New Test Setting)
- Directory
- Comments / Like
- Files
- Enricher Text
- Markdown Editor
- Handling Old Module Loading Fails
- Check CSRF Protection
- Update Docs
- Change licence class header 
- Add Var Types to View Files
- 


## Bugs

- Log Table not created on Installation


## Core Changes

- All HumHub Core Code is moved to protected/humhub
- Core Module folder was changed to core  (protected/humhub/core)
- Wall Core Module was renamed to Content



## Yii 2.0 

See Yii Migration Guide

Quick Notes:

- Use Namespaces!
- Yii::app() -> Yii::$app
- Use [] instead of array() - Optional
- Model: Validator  
 - Use array for multiple attributes
 - Validator changes Numeric->Integer ... 
 - Scenarios now in separate methods secenarios()
 - User::model()->findByPk($idy); -> User::findOne(['id'=>$id); 
 - Check beforeSave/afterSave when overwriting they may have parameters
 	- Better use $insert when available instead of $this->isNewRecord	
- Views:
	- ClientScript removed e.g. Yii::app()->clientScript->registerScriptFile
	- New Widget calls  WidgetClass::widget([options]) & echo it!
- Controllers
	-  Always return render action (also Widgets)
	-  camel case actions e.g. actionEditItem new Url: edit-item
	-  Easier: JSON Output
       Yii::$app->response->format = 'json'; return $json; 
- createUrl removed -> Url::to()
- CHtml methods reduced (e.g. no AjaxButton - use: \humhub\compat\widgets\AjaxButton instead
	

## Modules

### Structure

There are no changes on the Module itself.
We recommend to rename your Module Base Class e.g. ExampleModule.php to Module.php in Module Root folder.


### autostart.php

- 'import' section not longer used
- 'class' use full namespaced class name
- for events also use full namespaced classnames and new EVENT_ constants

Old autostart.php file:

```php
<?php
Yii::app()->moduleManager->register(array(
    'id' => 'activity',
    'class' => 'application.modules_core.activity.ActivityModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.activity.*',
        'application.modules_core.activity.models.*',
        'application.modules_core.activity.widgets.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onAfterDelete', 'callback' => array('ActivityModuleEvents', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('ActivityModuleEvents', 'onSpaceDelete')),
        array('class' => 'HActiveRecord', 'event' => 'onBeforeDelete', 'callback' => array('ActivityModuleEvents', 'onActiveRecordDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('ActivityModuleEvents', 'onIntegrityCheck')),
    ),
));
?>
```
 
New autostart.php file:

```php
<?php
use humhub\core\activity\Events;
use humhub\core\user\models\User;
use humhub\core\space\models\Space;
use humhub\components\ActiveRecord;
use humhub\commands\IntegrityController;

Yii::$app->moduleManager->register([
    'id' => 'activity',
    'class' => humhub\core\activity\Module::className(),
    'isCoreModule' => true,
    'events' => [
        ['class' => User::className(), 'event' => User::EVENT_AFTER_DELETE, 'callback' => [Events::className(), 'onUserDelete']],
        ['class' => Space::className(), 'event' => Space::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onSpaceDelete']],
        ['class' => ActiveRecord::className(), 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onActiveRecordDelete']],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::className(), 'onIntegrityCheck']],
    ],
]);
?>`
```


### DB Migrations

For migration scripts itself, there are no specific changes. 

If some of the modules classnames are stored in DB (e.g. content, activity or notification) table, create a db migration to change to namespaced classnames.

TODO: Add example migration script


### Translations

No HumHub specific changes, Check Yii2.


### Menus (Navigations)

TBD

### Stacks (Sidebars)

TBD

### Models

- New base classname for content records
> \humhub\core\content\components\activerecords\Content

- New base classname for content addon records
> \humhub\core\content\components\activerecords\ContentAddon


### User

We cleaned up the the humhub\core\user\components (\yii\web\User) class.
Use: Yii::$app->user->getIdentity() Instead: Yii::$app->user->getModel()

See humhub\core\user\components for more details.

### Widgets

- We recommend to remove the word Widget or/and the Module name from your classname when it's located in an own widget namespace.
E.g. ActivityStreamWidget -> Stream

### Urls

ContainerContainer like Space/User still provides the method createUrl to build URLs in container context (sguid/uguid).

All other createUrl method are also not longer available.

### Activities

Old Activity Example:
```php
<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    
<?php
echo Yii::t('PostModule.views_activities_PostCreated', '%displayName% created a new post.', array(
    '%displayName%' => '<strong>' . CHtml::encode($user->displayName) . '</strong>'
));
?>
<?php $this->endContent(); ?>
```

New Activity:

```php
<?php $this->beginContent('@humhub/core/activity/views/activityLayout.php', array('activity' => $activity)); ?>                 
<?php
echo Yii::t('PostModule.views_activities_PostCreated', '%displayName% created a new post.', array(
    '%displayName%' => '<strong>' . \yii\helpers\Html::encode($user->displayName) . '</strong>'
));
?>
<?php $this->endContent(); ?>

```


### HHtml Methods

#### EnrichText

Is now replaced by humhub\widgets\RichText

Old:
```
echo HHtml::enrichTest($text);
```

New:
```
echo humhub\widgets\RichText::widget(['text' => $text]);
```

### TimeAgo

Old:

```
echo HHtml::timeAgo($time);
```

New:

```
echo \humhub\widgets\TimeAgo::widget(['timestamp' => $time]);
```



### Yii1 Compatiblity Classes

#### Ajax Button

There is an CHtml::AjaxButton alternative available

```php

echo \humhub\compat\widgets\AjaxButton::widget([
    'label' => Yii::t('UserModule.views_profile_cropProfileImage', 'Save'),
    'ajaxOptions' => [
        'type' => 'POST',
        'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
        'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
        'url' => $space->createUrl('/space/admin/crop-image'),
    ],
    'htmlOptions' => [
        'class' => 'btn btn-primary'
    ]
]);

```

#### CActiveForm

TBD

#### HForm / CForm

TBD

	








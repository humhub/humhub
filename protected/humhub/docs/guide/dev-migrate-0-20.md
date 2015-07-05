# Migration Guide to HumHub 0.20

Quick Guide about 
- Open Points ( ToDos, To Check, Bugs)
- Short Yii2 Change Summary
- HumHub Changes


## ToDo

### Migrate

- Modules
	- Uninstall
- Theming
	- Themed Image files User/Space Default Profile Image
	- Notification/Activity Mail Views
- Integritychecker
- Tests
- RichText
	- Max Length & minimal Option
- Markdown Editor
- Caching
	- HSetting
	- UserModel: CanApproveUsers
- Url Rewriting
- Use AssetBundels
- Delete
- cookieValidationKey Installer?
- Reimplement Access Controls
- 

### Bugs

- Modal Confirm doesn't disappear

### To Check

- Comment in new Window Mode / Like Link in Modules
- Check Timezone
- Check Delete
- Check how to handle unapproved/disabled users (Directory, Spaces)
- Test Paginations
	- Check/Fix Ajax Link Pager at like show users
- CSRF
- Registration Process
	- When Invited to a space (Notification & Co.)
- Check unapproved users (Space, etc.)

## Core Changes

- All HumHub Core Code is moved to protected/humhub
- Core Module folder was changed to core  (protected/humhub/core)
- Wall Core Module was renamed to Content


## Modules

### Structure

There are no changes on the Module itself.
We recommend to rename your Module Base Class e.g. ExampleModule.php to Module.php in Module Root folder.


### config.php (autostart.php)

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

#### Tablenames

- user_mentioned
- ...



### Translations

No HumHub specific changes, Check Yii2.


### Menus (Navigations)

TBD

### Stacks (Sidebars)

TBD

### Models

- New base classname for content records
> [[\humhub\core\content\components\activerecords\Content]]

- New base classname for content addon records
> [[\humhub\core\content\components\activerecords\ContentAddon]]


### Content & ContentAddon

getContentTitle is now divided into

- getContentTitle
- getContentPreview





### User

We cleaned up the the humhub\core\user\components (\yii\web\User) class.
Use: Yii::$app->user->getIdentity() Instead: Yii::$app->user->getModel()

See [[\humhub\core\user\components\User]] for more details.

### Widgets

- We recommend to remove the word Widget or/and the Module name from your classname when it's located in an own widget namespace.
E.g. ActivityStreamWidget -> [[\humhub\core\activity\widgets\Stream]]

### Urls

[[\humhub\core\content\components\activerecords\ContentContainer::createUrl]] (Space/User) still provides the method createUrl to build URLs in container context (sguid/uguid).

All other createUrl method are also not longer available.

### Activities

See [[dev-module-activities.md]] about the Activity module.

To migrate you also need to change the class of your Activity:

Example Migration

```php
$this->update('activity', ['class' => 'exampleModule\activities\MyActivity'], ['class' => 'OldActivityName']);
```



### HHtml Methods

#### EnrichText

Is now replaced by humhub\widgets\RichText

Old:

```php
echo HHtml::enrichTest($text);
```

New:

```php
echo humhub\widgets\RichText::widget(['text' => $text]);
```

#### TimeAgo

Old:

```php
echo HHtml::timeAgo($time);
```

New:

```php
echo \humhub\widgets\TimeAgo::widget(['timestamp' => $time]);
```

#### postLink

New:

```php
echo Html::a($label, $url, ['data-method'=>'POST']);
```



### Modules

#### General

- Base Class: \humhub\components\Module


#### Assets

##### Publishing

The asset/resources folder will not longer automatically published.

Find more details about assets here:
http://www.yiiframework.com/doc-2.0/guide-structure-assets.html

##### Path
The default path for module resources (javascripts, images, css, ...) was changed from asset to resources. ('/modules/example/resources').
Also all HumHub module related files like Module Image or Screenshots should be located there.

You can change this path back to 'assets' by overwriting the 'resourcesPath' Attribute in your Module class.

e.g.

```php
class Module extends \humhub\components\Module
{

    public $resourcesPath = 'assets';
           
```

#### Configuration 

The URL handling to configure your module in **Administration -> Module** has changed.

The method "getConfigUrl" in Module class is not longer used.
Set configRoute attribute instead.

e.g.


```php
class Module extends \humhub\components\Module
{
    public $configRoute = '/example/admin/config';
           
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






## Yii 2.0 

See Yii Migration Guide

Quick Notes:

- Use Namespaces!
- Yii::app() -> Yii::$app
- Use [] instead of array() - Optional
- Model: Validator  
 - Use array for multiple attributes
 - Validator changes Numeric->Integer ... 
 - String validator doesn't allow Integer Types (cast!)
 - Scenarios now in separate methods secenarios()
 - User::model()->findByPk($idy); -> User::findOne(['id'=>$id); 
 - Check beforeSave/afterSave when overwriting they may have parameters
 	- Better use $insert when available instead of $this->isNewRecord	
 - Make tableName method static 
 
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
- Behaviors
	- $this->getOwner() replaced by $this->owner
	





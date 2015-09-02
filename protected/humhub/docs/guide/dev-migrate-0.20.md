# Module Migration Guide to HumHub 0.20

This document briefly describes the module api changes in HumHub 0.20.

Since HumHub 0.20 is now based on Yii 2 you also need to migrate your modules to it.

See more information about Yii 2.0 here: 
- [Yii: Upgrading from Version 1.1](http://www.yiiframework.com/doc-2.0/guide-intro-upgrade-from-v1.html)
- [Yii2 Migration Notes](dev-migrate-0.20-yii2.md)

## Migration

### General

- Namespace classes e.g. module\example or create own Namespace
- Migrate to Yii 2.0 (Controllers, Models, Views, Assets, ...) 
- Migrate to HumHub Namespaced classes
- Raname e.g. ExampleModule.php && ExampleEvents.php   to Module.php / Events.php
- Changed former autostart.php to config.php: [config.php](dev-module-index.md#configphp)
- Migrate database (see below)
 

### Database / Migrations

Also the existing migration script needs to use Namespaces now.

Because also class names of your module may stored in database (e.g. for Activities, Likes, Notification, ...)
It's neccessary to create a new migration derived from [[humhub\components\Migration]] which uses **renameClass** method class to fix these records.

Example Migration:

```php
<?php

use humhub\components\Migration;
use module\wiki\models\WikiPage;
use module\wiki\models\WikiPageRevision;

class m150705_081309_namespace extends Migration
{

    public function up()
    {
        $this->renameClass('WikiPage', WikiPage::className());
        $this->renameClass('WikiPageRevision', WikiPageRevision::className());
    }

    public function down()
    {
        echo "m150705_081309_namespace cannot be reverted.\n";

        return false;
    }

}
```


### Content & ContentAddon

getContentTitle is now divided into

- getContentTitle
- getContentPreview


### autostart.php

TBD

- Renamed to config.php
- Removed imports
- Return array
- Namespaces


### Urls

[[\humhub\modules\content\components\activerecords\ContentContainer::createUrl]] (Space/User) still provides the method createUrl to build URLs in container context (sguid/uguid).

Use [http://www.yiiframework.com/doc-2.0/yii-helpers-url.html](http://www.yiiframework.com/doc-2.0/yii-helpers-url.html) for other URLs.

### Activities

Please recreate Activities as described here: [[dev-module-activities.md]]

Since 0.20 there is an automatic "created" Activity for Content. 


To migrate existing Activities to the new Scheme you need to create an migration.

Example Migration:

```php
// Rename Custom Activities
$this->update('activity', ['class' => 'exampleModule\activities\MyActivity'], ['class' => 'OldActivityName']);

// Rename own "Created" Activities to core 
$this->update('activity', ['class' => 'humhub\modules\content\activities\ContentCreated', 'module' => 'content'], ['class' => 'PollCreated']);


```


### Assets

The asset/resources folder will not longer automatically published.

Find more details about assets here:
http://www.yiiframework.com/doc-2.0/guide-structure-assets.html

The default path for module resources (javascripts, images, css, ...) was changed from asset to resources. ('/modules/example/resources').
Also all HumHub module related files like Module Image or Screenshots should be located there.

You can change this path back to 'assets' by overwriting the 'resourcesPath' Attribute in your Module class.

e.g.

```php
class Module extends \humhub\components\Module
{

    public $resourcesPath = 'assets';
           
```

### Module Configuration 

The URL handling to configure your module in **Administration -> Module** has changed.

The method "getConfigUrl" in Module class is not longer used.
Set configRoute attribute instead.

e.g.


```php
class Module extends \humhub\components\Module
{
    public $configRoute = '/example/admin/config';
           
```


## (Incomplete) List of changed Helpers/Widgets:

#### EnrichText
```php
// New
echo humhub\widgets\RichText::widget(['text' => $text]);

// Old
echo HHtml::enrichTest($text);
```

#### TimeAgo
```php
// New
echo \humhub\widgets\TimeAgo::widget(['timestamp' => $time]);

// Old
echo HHtml::timeAgo($time);
```

#### PostLink
```php
// New
echo Html::a($label, $url, ['data-method'=>'POST']);

// Old:
echo HHtml::postLink(...);
```

#### AjaxButton/Submit/...
```php
echo \humhub\widgets\AjaxButton::widget([
    'label' => "Save",
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

#### Yii::app()->input->stripClean

Stripclean is not longer available. use Html::encode() user input on output _and_ create validators.


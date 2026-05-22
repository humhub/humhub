# Streams

Streams are used to asynchronously load batches of content entries which may can be filtered or sorted. 
The most common type of stream is the WallStream used for example on the dashboard or space stream view.
Besides WallStreams the HumHub core only implements one additional stream type, the activity sidebar stream.
The following section describes how to implement custom streams and extend existing streams.

## Stream Channel

The `Content::$stream_channel` property defines the default stream type of this content. If not
otherwise defined the 'default' stream channel will be used, which means this content will be included in wall streams as
the space, profile or dashboard stream.

The `ContentActiveRecord::$streamChannel` property of your custom content type can be used to overwrite the default stream type.
Its common practice to set `$this->streamChannel  = null` for certain records in order to exclude specific entries from the
default wall streams. An example of a custom stream channel is the activity stream. Activity records are not included in
the wall streams but are part of an activity sidebar stream.

**Exclude all instances from default wall streams:**

```php
class MyContent extends ContentActiveRecord
{
    public $streamChannel = null;
    
    //...
}
```

**Exclude specific entries from default wall streams:**

```php
class MyContent extends ContentActiveRecord
{
    public function beforeSave($insert)
    {
        if($this->isNotImportant()) {
          $this->streamChannel = null;
        }

        return parent::beforeSave($insert);
    }
    
    //...
}
```

For your custom `ContentActiveRecord` you can consider the following stream channel options:

- **default**: this stream channel will include your content to space/profile walls and the dashboard
- **null** will exclude the content from the default wall streams (space/profile/dashboard)
- Use a custom stream channel if you exclusively want your content to be included in your own custom stream

 Note: A custom stream channel should be unique, so choose a meaningful name preferably with module prefix.

## StreamEntryWidget

A `StreamEntryWidget` is responsible for rendering stream entries. The `StreamEntryWidget` class
is the base widget class for all types of stream entries. The default stream widget class of a content type
is defined by the `ContentActiveRecord::$wallEntryClass` property. For wall streams there are two different widget 
types available, the `WallStreamEntryWidget` and `WallStreamModuleEntryWidget`. 

### WallStreamEntryWidget

The `WallStreamEntryWidget` renders a post style wall entry with user image and name in the head part of the wall entry.
This widget type should be used for content which emphasizes the author of the content and contains mainly personal 
(not collaborative) content. For content with collaborative nature you should rather use the `WallStreamModuleEntryWidget`
class.

When extending a `WallStreamEntryWidget` you'll have to implement the `WallStreamEntryWidget::renderContent()` function
which will be embedded in the content section of your stream entry.

**Example:**

````php
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;

class WallEntry extends WallStreamEntryWidget
{
    protected function renderContent()
    {
        return $this->render('wallEntry', ['model' => $this->model]);
    }
}
````

### WallStreamModuleEntryWidget

The `WallStreamModuleEntryWidget` does not emphasize the user, but instead the content type and the content title. 
This widget should be used for content types in which the author is not that important as for example a wiki or other 
collaborative content.

When extending `WallStreamModuleEntryWidget` you'll have to implement the `WallStreamEntryWidget::renderContent()` as
well as the `WallStreamEntryWidget::getTitle()` function. You may also want to overwrite the `WallStreamEntryWidget::getIcon()`
function to overwrite the default icon provided by `ContentActiveRecord::getIcon()`.

**Example:**

```php
use humhub\modules\content\widgets\stream\WallStreamModuleEntryWidget;

class WallEntry extends WallStreamModuleEntryWidget
{
    protected function renderContent()
    {
        return $this->render('wallEntry', ['model' => $this->model]);
    }

    protected function getIcon()
    {
        // By default we do not have to overwrite this function unless we want to overwrite the default ContentActiveRecord::$icon
        return 'tasks';
    }

    protected function getTitle()
    {
        return $this->model->title;
    }
}
```
 
### WallEntryControls

The WallEntryControls menu is part of all wall stream entries and includes stream links as Delete or Edit. 
The following links are added to the wall entry by default (depending on the user permission and view context):

 - **PermaLink**: Opens a modal with the content url
 - **DeleteLink**: Used to delete an entry
 - **EditLink**: Used to edit an entry
 - **VisibilityLink**: Used to switch the visibility of a content
 - **NotificationSwitchLink**: Used enable/disable receiving notifications for a content.
 - **PinLink**: Used to pin/unpin content entries to a stream
 - **MoveContentLink**: Used to move a content entry to another space
 - **ArchiveLink**: Used to archive/unarchive an entry
 - **TopicLink**: Used to manage topics of a content entry 

By overwriting `WallStreamModuleEntryWidget::getControlsMenuEntries()` your content type can add additional menu entries to
the controls menu of your content type follows:

```php
public function getControlsMenuEntries()
{
    $result = parent::getControlsMenuEntries();
    $result[] = new MySpecialLink()
    return $result;
}
```

You can also disable default entries as follows:

````php
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;

class WallEntry extends WallStreamEntryWidget
{
    //...

    protected function init()
    {
        parent::init(); 
        // Exclude the delete link from the controls menu
        $this->renderOptions->disableControlsDelete();
    }
}
````

### WallStreamEntryOptions

The `StreamEntryWidget::$renderOptions` property can be used to configure the appearance of an wall entry, for example by:

 - Disabling controls menu entries
 - Disabling the whole controls menu
 - Disabling certain stream entry addons
 - Changing the output depending on the view context
 
#### View context

The appearance of a stream entry may differentiate depending on a view context configuration. Wall entries on the dashboard for example
include further information about the container the content is assigned to. Posts in the detail view display the whole content
without the use of a collapsed read-more section. The default streams support the following view contexts:

 -  `StreamEntryOptions::VIEW_CONTEXT_DEFAULT`: Default appearance (e.g. on space/profile stream)
 -  `StreamEntryOptions::VIEW_CONTEXT_DASHBOARD`: Dashboard stream
 -  `StreamEntryOptions::VIEW_CONTEXT_SEARCH`: Content search stream
 -  `StreamEntryOptions::VIEW_CONTEXT_DETAIL`: Detail view (single entry stream)
 -  `StreamEntryOptions::VIEW_CONTEXT_MODAL`: Rendered within a modal dialog
 
**Example:**

````php
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;

class WallEntry extends WallStreamEntryWidget
{
    //...

    protected function getControlsMenuEntries()
    {
        $result = parent::getControlsMenuEntries();
            
        // Only add this link in default (e.g. space) context
        if($this->renderOptions->isViewContext(StreamEntryOptions::VIEW_CONTEXT_DEFAULT) {
           $result[] = new MySpecialLink(['model' => $this->model])
        }

        return $result;
    }
}
````

### editRoute and editMode

If a `WallStreamEntryWidget::$editRoute` is defined an edit menu item will be added to the controls menu in case the current
user is allowed to edit the content. The `WallStreamEntryWidget::$editMode` defines how the content should be edited e.g.
within a modal, inline, or within a new page.

There are the following edit modes available:

 - `EDIT_MODE_MODAL` the response of `editRoute` action will be loaded into a modal.
 - `EDIT_MODE_INLINE` the response of `editRoute` action will be embeded into the WallEntry content.
 - `EDIT_MODE_NEW_WINDOW` the page response of `editRoute` action will be fully loaded.

## Custom streams

You can add custom streams as for example own wall or sidebar streams to your custom module. A custom stream implementation
contains of the following components:

 - **Stream action** responsible for handling stream requests
 - **StreamQuery** responsible for fetching and filtering the stream result
 - **StreamViewer** widget responsible for rendering the stream container in the view
 - **Stream filter navigation** (optional) can be used to render a filter navigation for your stream

The following sections explain the different components of a stream by implementing a custom content info stream. Our custom
stream will render stream entries in form of content metadata blocks by extending the dashboard stream. Furthermore,
we'll add an additional custom filter to only include content created by the current user (if active).

### StreamViewer

The `humhub\modules\stream\widgets\StreamViewer` widget is used to render our custom stream. Note, the stream entries of
a stream are loaded asynchronously and therefore will not directly be rendered by the StreamViewer widget itself. 
The StreamViewer widget expects a `streamAction` property pointing to the controller action handling stream requests. 
The optional `streamFilterNavigation` can be used to define a stream filter navigation widget class. 
Wall streams come with a default stream filter navigation. In case you want to disable the default navigation
for your custom stream, just set `streamFilterNavigation` to false or null.

**views/index/index.php:**

```php
<?= StreamViewer::widget([
    'streamAction' => '/mymodule/stream/stream',
    'streamFilterNavigation' => ContentInfoStreamFilterNavigation::class
])?>
```

### Stream filter navigation

The stream filter navigation can be used to add additional filter options to your stream. In our content info stream
we replace the default filter navigation with a custom filter navigation widget. The filter navigation will include
a single checkbox filter which will only include content created by the current user if active.

**widgets\ContentInfoStreamFilterNavigation:**

```php
class ContentInfoStreamFilterNavigation extends FilterNavigation
{

    protected function initFilterPanels(){}

    protected function initFilterBlocks(){}

    protected function initFilters(){}

    public function getAttributes()
    {
        return [
            'style' => 'padding-left:15px'
        ];
    }
}
```

In our example we render a very simple and static filter navigation, for more complex and extendable stream navigations, 
you might want to work with separated filter blocks and panels, see `humhub\modules\stream\widgets\WallStreamFilterNavigation.`
In such cases we would register the filters within `initFilters` instead of directly rendering them in the view.

**widgets\views\filterNavigation.php:**

The view of our filter navigation widget looks like the following:

```php
<?= Html::beginTag('div', $options)?>

    <?= CheckboxFilterInput::widget([
        'id' => OwnContentStreamFilter::FILTER_ID,
        'title' => Yii::t('ContentInfoModule.base','Only show my own content')
    ])?>

<?= Html::endTag('div')?>
```

The filter in the previous example will be added to the default `filters[]` request parameter. 
In case you want to use another parameter you need to overwrite the `category` property of your filter input.

### Stream filter implementation

Stream filters extend `humhub\modules\stream\models\filters\StreamQueryFilter` and can be used to add query conditions
to your stream query. In the following example we implement our `filter_my_content` filter.

```php
class OwnContentStreamFilter extends StreamQueryFilter
{
    const FILTER_ID = 'filter_my_content';

    public $filters = [];

    public function rules()
    {
        return [
            ['filters', 'safe']
        ];
    }

    public function apply()
    {
        if($this->filters === static::FILTER_ID || in_array(static::FILTER_ID, $this->filters, true)) {
            $this->streamQuery->query()->andWhere(['content.created_by' => Yii::$app->user->id]);
        }
    }
}
```

### Stream controller

The `humhub\modules\stream\actions\Stream` is the base [controller action class](https://www.yiiframework.com/doc/guide/2.0/en/structure-controllers#standalone-actions)
for all streams responsible for handling stream results. The HumHub core provides the following default stream actions:

 - `ContentContainerStream`: Includes an optional content container filter and should be used for streams on container 
 level, e.g. include all content of content type x in space y.
 - `DashboardStreamAction`: Includes dashboard content visibility filters

**Example Controller**

In this example we register our action and register our custom filter handler. Furthermore, we overwrite the widget class used 
to render the stream entries.

```php
class StreamController extends Controller
{
    public function actions()
    {
        return [
            'stream' => [
                'class' => DashboardStreamAction::class,
                'filterHandlers' => [OwnContentStreamFilter::class],
                'streamEntryOptions' => (new WallStreamEntryOptions)
                   ->overwriteWidgetClass(ContentInfoWallStreamEntryWidget::class)
            ],
        ];
    }
}
```

### Stream action

In case you have a more complex custom stream scenario, or want your stream action to be extendable by events you can consider
implementing a custom stream action as follows:

```php
class ContentInfoStreamAction extends DashboardStreamAction
{
   protected function initStreamEntryOptions()
   {
        return (new WallStreamEntryOptions)
             ->overwriteWidgetClass(ContentInfoWallStreamEntryWidget::class);
   }

   protected function initQuery($options = [])
   {
       $query = parent::initQuery($options);
       $query->addFilterHandler(OwnContentStreamFilter::class);
       return $query;
   }
}
```

### Stream query

You can also consider implementing a reusable stream query class as follows:

```php
class ContentInfoStreamAction extends ContentContainerStream
{
   public $streamQuery = MyStreamQuery::class;
   
   protected function initStreamEntryOptions()
   {
       return (new WallStreamEntryOptions)
          ->overwriteWidgetClass(ContentInfoWallStreamEntryWidget::class);
   }
}
```
```php
class ContentInfoStreamQuery extends ContentContainerStreamQuery
{
   protected function beforeApplyFilters()
   {
       $this->addFilterHandler(MyStreamFilter::class);
       parent::beforeApplyFilters();
   }
}
```

### Stream entry widget

Since we do not want to use the default entry layout in our content info stream, we need to implement a custom `StreamEntryWidget`:

**widgets\ContentInfoWallStreamEntryWidget:**

```php
use humhub\modules\content\widgets\stream\StreamEntryWidget;

class ContentInfoWallStreamEntryWidget extends StreamEntryWidget
{
    protected function renderBody()
    {
        return $this->render('contentInfoWallStreamEntry', [
            'model' => $this->model
        ]);
    }
}
```

**widgets\views\contentInfoWallStreamEntry:**

```php
<?php
use humhub\libs\Html;
use humhub\modules\content\widgets\VisibilityIcon; ?>

<div style="border:1px solid var(--info);margin:10px;padding:10px;">
    <b>Content id:</b> <?= $model->content->id ?><br>
    <b>Content name:</b> <?= $model->getContentName() ?> <?= VisibilityIcon::getByModel($model) ?><br>
    <b>Created at:</b> <?=  Yii::$app->formatter->asDatetime($model->content->created_at) ?><br>
    <b>Created by:</b> <?=  Html::containerLink($model->content->createdBy) ?><br>
    <?php if($model->content->isUpdated()) :?>
        <b>Last updated at:</b> <?=  Yii::$app->formatter->asDatetime($model->content->updated_at) ?><br>
        <b>Updated by:</b> <?=  Html::containerLink($model->content->updatedBy) ?><br>
    <?php endif; ?>
    <b>Container:</b> <?=  Html::containerLink($model->content->container) ?><br>
</div>
```

## Extend stream filters

Since HumHub v1.3 you are able to extend the stream filter navigations by implementing following event handlers: 

- `WallStreamQuery::EVENT_BEFORE_FILTER` to add the filter to the query
- `WallStreamFilterNavigation::EVENT_BEFORE_RUN`

The `WallStreamFilterNavigation` navigation contains three **filterPanels**:

- `WallStreamFilterNavigation::PANEL_POSITION_LEFT`
- `WallStreamFilterNavigation::PANEL_POSITION_CENTER`
- `WallStreamFilterNavigation::PANEL_POSITION_RIGHT`

Each panel can contain multiple **filterBlocks**. Each block may contain multiple filters sorted by a `sortOrder`
setting. The following example adds an `originator` filter to the wall stream:

**Event configuration:**

```php
return [
    [
        'class' => \humhub\modules\stream\models\WallStreamQuery::class,
        'event' =>  \humhub\modules\stream\models\WallStreamQuery::EVENT_BEFORE_FILTER,
        'callback' => ['\humhub\modules\demo\Events', 'onStreamFilterBeforeFilter'],
    ],
    [
        'class' => \humhub\modules\stream\widgets\WallStreamFilterNavigation::class,
        'event' =>  \humhub\modules\stream\widgets\WallStreamFilterNavigation::EVENT_BEFORE_RUN,
        'callback' => ['\humhub\modules\demo\Events', 'onStreamFilterBeforeRun'],
    ]
]
```

**Event handler implementation:**

```php
class Events extends \yii\base\Object
{
    const FILTER_BLOCK_ORIGINATOR = 'originator';
    const FILTER_ORIGINATOR = 'originator';
    
    public static function onStreamFilterBeforeRun($event)
    {
        /** @var $wallFilterNavigation WallStreamFilterNavigation */
        $wallFilterNavigation = $event->sender;
    
        // Add a new filter block to the last filter panel
        $wallFilterNavigation->addFilterBlock(static::FILTER_BLOCK_ORIGINATOR, [
            'title' => 'Originator',
            'sortOrder' => 300
        ], WallStreamFilterNavigation::PANEL_POSITION_RIGHT);
    
        // Add a filter of type PickerFilterInput to the new filter block
        $wallFilterNavigation->addFilter([
            'id' => static::FILTER_ORIGINATOR,
            'class' => PickerFilterInput::class,
            'picker' => UserPickerField::class,
            'category' => 'originators',
            'pickerOptions' => [
                'id' => 'stream-user-picker',
                'itemKey' => 'id',
                'name' => 'stream-user-picker'
            ]], static::FILTER_BLOCK_ORIGINATOR);
    }
    
    public static function onStreamFilterBeforeFilter($event)
    {
        /** @var $streamQuery WallStreamQuery */
        $streamQuery = $event->sender;
    
        // Add a new filterHandler to WallStreamQuery
        $streamQuery->addFilterHandler(OriginatorStreamFilter::class);
    }
}
```

**OriginatorStreamFilter.php**

```php
class OriginatorStreamFilter extends StreamQueryFilter
{
    public $originators = [];

    public function rules() {
        return [
            [['originators'], 'safe']
        ];
    }

    public function apply()
    {
        if(empty($this->originators)) {
            return;
        }

        if($this->originators instanceof User) {
            $this->originators = [$this->originators->id];
        } else if(!is_array($this->originators)) {
            $this->originators = [$this->originators];
        }

        $this->query->joinWith('contentContainer');

        if (count($this->originators) === 1) {
            $this->query->andWhere(["user.guid" => $this->originators[0]]);
        } else if (!empty($this->originators)) {
            $this->query->andWhere(['IN', 'user.guid', $this->originators]);
        }
    }
}
```

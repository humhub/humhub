Content Streams
=================
Streams are used to asynchronously load batches of content entries which can be filtered or sorted. 
The stream concept is used for example in _space and profile walls_, the _dashboard_ and
_activity stream_.

## Stream Channel

The `stream_channel` attribute of a [[humhub\modules\content\models\Content]] entry defines the relation of this content to
a specific type of stream. The `default` stream channel for example is used by _space_, _user_ and _dashboard_
streams whereas the `activity` stream-channel is exclusively used in activity streams.

The `stream_channel` of your content type can be overwritten by setting the [[humhub\modules\content\components\ContentActiveRecord::streamChannel|ContentActiveRecord::streamChannel]] attribute.

For own `ContentActiveRecord` you can consider the following `stream_channel` options:

- `default` this stream_channel will include your content to default _space/profile walls_ and the _dashboard_. You are still able to create a custom stream view which filters content by type.
- `null` will exclude the content from the default streams
- Use a custom stream channel if you exclusively want your content to be included in your own custom stream (similar to the activity concept).

> Note: A custom stream channel should be unique, so choose a meaningful name preferably with module prefix.

## WallEntry Widget

A [[humhub\modules\content\widgets\WallEntry|WallEntry widget]] is responsible for rendering the individual `stream entries`
of a `stream` and is defined by [[humhub\modules\content\components\ContentActiveRecord::wallEntryClass|ContentActiveRecord::wallEntryClass]].

The following example shows a very basic WallEntry widget implementation.

> Note: By default your WallEntry view only have to render the actual content, the default WallEntry layout is available in `@humhub/modules/content/widgets/views/wallEntry.php`

**mymodule\widgets\WallEntry.php**:

```php
class WallEntry extends \humhub\modules\content\widgets\WallEntry
{
    public function run()
    {
        return $this->render('wallEntry', [
                'model' => $this->contentObject
        ]);
    }
}
```

**mymodule\widgets\views\wallEntry.php**:

```php
<div>
    <?= $model->title ?>
    <?= $model->myContent ?>
    ...
</div>
```

The `WallEntry` widget will be provided with a [[humhub\modules\content\widgets\WallEntry::contentObject|contentObject]] which holds the
`ContentActiveRecord` model to be rendered.

Your `WallEntry` widget class can also set the following attributes:

 - [[humhub\modules\content\widgets\WallEntry::editRoute|editRoute]] defines an edit route to your edit action which will be used to render an edit link (see WallEntryControls section)
 - [[humhub\modules\content\widgets\WallEntry::editMode|editMode]] defines the way the edit action is triggered (see WallEntryControls section)
 - [[humhub\modules\content\widgets\WallEntry::wallEntryLayout|wallEntryLayout]] defines the layout used to embed the result of `render()`, by default you only have to care about rendering the content section of your WallEntry


### WallEntryControls

The default WallEntry layout contains a context menu with content actions like `edit`, `delete`, `archive` etc.
This menu can be manipulated by overwriting the [[humhub\modules\content\widgets\WallEntry::getContextMenu()|getContextMenu()]] function and 
or use the [[humhub\modules\content\widgets\WallEntry::controlsOptions|controlsOptions]] property as in the following example.

By setting the [[humhub\modules\content\widgets\WallEntry::editRoute|editRoute]] we automatically add an edit link to our WallEntryControls in
case the current user is allowed to edit the content. The type of the edit action is defined by the [[humhub\modules\content\widgets\WallEntry::editMode|editMode]].

There are the following edit modes available:

 - `EDIT_MODE_MODAL` the response of `editRoute` will be loaded into a modal.
 - `EDIT_MODE_INLINE` the response of `editRoute` will be embeded into the WallEntry content.
 - `EDIT_MODE_NEW_WINDOW` the page response of `editRoute` will be fully loaded.
 
```php
class WallEntry extends \humhub\modules\content\widgets\WallEntry
{
    public $editRoute = "/my-module/entry/edit";
    
    public $editMode = self::EDIT_MODE_MODAL;
    
    // Will prevent the default DeleteLink and always add a MySpecialLink
    $this->controlsOptions = [
        'prevent' => [\humhub\modules\content\widgets\DeleteLink::class],
        'add' => [MySpecialLink::class]
    ];
    
    //...
    
    public function getContextMenu()
    {
      $result = parent::getContextMenu();
      
      // Only add a CloseLink if the user is allowed to edit the content.
      if($this->contentObject->content->canEdit()) {
        $this->addControl($result, [CloseLink::class, ['model' => $this->contentObject], ['sortOrder' => 200]]);
      }
      
      return $result;
    ]
}
```

**CloseLink example**:

```php
class CloseLink extends humhub\modules\content\widgets\WallEntryControlLink
{
    public $model;
    
    public function init()
    {
        if($this->model->closed) {
            $this->label = Yii::t('MyModule.base', 'Reopen');
            $this->icon = 'fa-check';
        } else {
            $this->label = Yii::t('MyModule.base', 'Close');
            $this->icon = 'fa-times';
        }
        
        $this->options = [
            // set some further html options
        ];
        
        parent::init();
    }
}
```

## Create Module Content Streams

### Implement StreamAction

Derived from [[humhub\modules\content\components\actions\ContentContainerStream]]

A `StreamAction` is responsible for handling a stream request and filtering stream entries.
The following example extends the default [[humhub\modules\content\components\actions\ContentContainerStream|ContentContainerStream]] and
adds an content-type filter:

```php
namespace mymodule\actions;

use humhub\modules\content\components\actions\ContentContainerStream;

class StreamAction extends ContentContainerStream
{
    public function setupFilters()
    {
		// Limit output to specific content type
        $this->activeQuery->andWhere(['content.object_model' => MyModel::class]);
    }
}
```

Add the `StreamAction` to your Controller:

```php
class StreamController extends ContentContainerController
{

    public function actions()
    {
        return [
            'stream' => [
                'class' => StreamAction::class,
                'contentContainer' => $this->contentContainer
            ],
        ];
    }
```

### Display Stream

You can use the [[humhub\modules\stream\widgets\StreamViewer|StreamViewer]] widget to display your stream within your view as follows:

```php

<?= \humhub\modules\stream\widgets\StreamViewer::widget([
    'contentContainer' => $contentContainer,
    'streamAction' => '/mymodule/stream/stream',
    'messageStreamEmpty' => ($contentContainer->canWrite()) ?
            Yii::t('PollsModule.widgets_views_stream', '<b>There are no polls yet!</b><br>Be the first and create one...') :
            Yii::t('PollsModule.widgets_views_stream', '<b>There are no polls yet!</b>'),
    'messageStreamEmptyCss' => ($contentContainer->canWrite()) ? 'placeholder-empty-stream' : '',
]); ?>

```

## Create Content Form

You can add a [[humhub\modules\content\widgets\WallCreateContentForm|WallCreateContentForm]] on top of your custom `stream` in
order to create new stream-entries within your stream view.

### Create Form Widget

Create a Form Widget derived from [[humhub\modules\content\widgets\WallCreateContentForm]]

```php

namespace mymodule\widgets;

use humhub\modules\content\widgets\WallCreateContentForm;

class WallCreateForm extends WallCreateContentForm
{

    public $submitUrl = '/mymodule/mymodel/create';

    public function renderForm()
    {
        // Render your custom form here
        return $this->render('form', []);
    }

}

```

Create a widget `view` which contains module specific fields. All standard fields (e.g. visibility) are added automatically.

```php
<?= Html::textArea("question", "", ['id' => 'contentForm_question', 'class' => 'form-control autosize contentForm', 'rows' => '1', "tabindex" => "1", "placeholder" => Yii::t('PollsModule.widgets_views_pollForm', "Ask something..."])); ?>

<div class="contentForm_options">
    <?= Html::textArea("answersText", "", ['id' => "contentForm_answersText", 'rows' => '5', 'style' => 'height: auto !important;', "class" => "form-control contentForm", "tabindex" => "2", "placeholder" => Yii::t('PollsModule.widgets_views_pollForm', "Possible answers (one per line)")]); ?>
    <div class="checkbox">
        <label>
            <?= Html::checkbox("allowMultiple", "", ['id' => "contentForm_allowMultiple", 'class' => 'checkbox contentForm', "tabindex" => "4"]); ?> <?= Yii::t('PollsModule.widgets_views_pollForm', 'Allow multiple answers per user?'); ?>
        </label>
    </div>
</div>
```

### Create Action

Create an action in your modules controller to receive form inputs.

All default tasks (e.g. access validation, ContentContainer assignment) are handled by [[humhub\modules\content\widgets\WallCreateContentForm::create()]]


Example:

```php

public function actionCreate()
{
    $model = new MyModel();
    $model->question = Yii::$app->request->post('question');
    $model->answersText = Yii::$app->request->post('answersText');
    $model->allow_multiple = Yii::$app->request->post('allowMultiple', 0);

    return \mymodule\widgets\WallCreateForm::create($model);
}

```

### Display Form

Place the Form widget above the Stream widget in your view.

e.g.

```php
<?= \humhub\modules\polls\widgets\WallCreateForm::widget(array('contentContainer' => $contentContainer)); ?>
```

## Stream Filter (sinve v1.3)

Since HumHub v1.3 you are able to extend the stream filter by listening to 

- `\humhub\modules\stream\models\WallStreamQuery::EVENT_BEFORE_FILTER` to add the filter to the query
- `humhub\modules\stream\widgets\WallStreamFilterNavigation::EVENT_BEFORE_RUN`

The [[humhub\modules\stream\widgets\WallStreamFilterNavigation]] class is of type [[ humhub\modules\ui\filter\widgets\FilterNavigation]].
A `Filternavigation` consists of `filterPanels` and `filterBlocks`. The `WallStreamFilterNavigation` navigation for example contains three `filterPanels`

- `WallStreamFilterNavigation::PANEL_POSITION_LEFT`
- `WallStreamFilterNavigation::PANEL_POSITION_CENTER`
- `WallStreamFilterNavigation::PANEL_POSITION_RIGHT`

and multiple `filterBlocks` containing the actual filters assigned to a specific panel and sorted by a `sortOrder`.

The following example adds a `originator` filter to the wall stream:

Event configuration:

```php
[
    'class' => \humhub\modules\stream\models\WallStreamQuery::class,
    'event' =>  \humhub\modules\stream\models\WallStreamQuery::EVENT_BEFORE_FILTER,
    'callback' => ['\humhub\modules\demo\Events', 'onStreamFilterBeforeFilter'],
],
[
    'class' => \humhub\modules\stream\widgets\WallStreamFilterNavigation::class,
    'event' =>  \humhub\modules\stream\widgets\WallStreamFilterNavigation::EVENT_BEFORE_RUN,
    'callback' => ['\humhub\modules\demo\Events', 'onStreamFilterBeforeRun'],
],
```

Event handlers:


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
        $streamQuery->filterHandlers[] = OriginatorStreamFilter::class;
    }
}
```

OriginatorStreamFilter.php

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

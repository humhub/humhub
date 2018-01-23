Content Streams
=================
Streams are used to asynchronously load batches of content entries which can be filtered or sorted. 
The stream concept is used for example in _space and profile walls_, the _dashboard_ and
_activity stream_.

Custom modules can use own streams for example to filter content by a specific type, or other custom
filters.

### Stream Channel

The `stream_channel` attribute of a [[humhub\modules\content\models\Content]] entry defines the relation of this content to
a specific type of stream. The `default` stream channel for example is used by _space/profile_ and _dashboard_
streams and the `activity` stream channel is exclusively used in activity streams.

The stream channel of your content type can be overwritten by setting the [[humhub\modules\content\components\ContentActiveRecord::streamChannel|ContentActiveRecord::streamChannel]] attribute.

You can consider the following stream channel options for your own [[humhub\modules\content\components\ContentActiveRecord|ContentActiveRecord]]:

- `default` stream channel will include your content to default _space/profile walls_ and the _dashboard_. You are still able to create a custom stream view which filters content by type.
- `null` will exclude the content from the default streams
- Use a custom stream channel if you exclusively want your content to be included in your own custom stream (similar to activity concept).
> Note: A custom stream channel should be unique, so choose a meaningful name preferably with module prefix.

### WallEntry Widget

A [[humhub\modules\content\widgets\WallEntry|WallEntry widget]] is responsible for rendering the individual stream entries
of a stream and is defined by [[humhub\modules\content\components\ContentActiveRecord::wallEntryClass|ContentActiveRecord::wallEntryClass]].

The following example shows a very basic WallEntry widget implementation.

> Note: By default your WallEntry view only have to render the actual content, the default WallEntry layout is available in `@humhub/modules/content/widgets/views/wallEntry.php`

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

wallEntry.php:
```php
<div>
    <?= $model->title ?>
    <?= $model->myContent ?>
    ...
</div>
```

The WallEntry widget will be provided with a [[humhub\modules\content\widgets\WallEntry::contentObject|contentObject]] which holds the
[humhub\modules\content\components\ContentActiveRecord|ContentActiveRecord]] model to be rendered.

Your [[humhub\modules\content\widgets\WallEntry|WallEntry]] class can also set the following attributes:

 - [[humhub\modules\content\widgets\WallEntry::editRoute|editRoute]] defines an edit route to your edit action which will be used to render an edit link (see WallEntryControls section)
 - [[humhub\modules\content\widgets\WallEntry::editMode|editMode]] defines the way the edit action is triggered (see WallEntryControls section)
 - [[humhub\modules\content\widgets\WallEntry::wallEntryLayout|wallEntryLayout]] defines the layout used to embed the result of `render()`, by default you only have to care about rendering the content section of your WallEntry


#### WallEntryControls

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

CloseLink example:

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

## Create own Module Content Stream

### Implement StreamAction

Derived from [[humhub\modules\content\components\actions\ContentContainerStream]]

Example:

```php
class StreamAction extends humhub\modules\content\components\actions\ContentContainerStream
{
    public function setupFilters()
    {
		// Limit output to specific content type
        $this->activeQuery->andWhere(['content.object_model' => Poll::className()]);
    }
}
```

Specify Action in Controller

Example:

```php
class PollController extends ContentContainerController
{

    public function actions()
    {
        return array(
            'stream' => array(
                'class' => \humhub\modules\polls\components\StreamAction::className(),
                'mode' => \humhub\modules\polls\components\StreamAction::MODE_NORMAL,
                'contentContainer' => $this->contentContainer
            ),
        );
    }
```

### Display Stream

You can use the Stream Widget to display the Stream in your View.

```php

echo \humhub\modules\content\widgets\Stream::widget(array(
    'contentContainer' => $contentContainer,
    'streamAction' => '//polls/poll/stream',
    'messageStreamEmpty' => ($contentContainer->canWrite()) ?
            Yii::t('PollsModule.widgets_views_stream', '<b>There are no polls yet!</b><br>Be the first and create one...') :
            Yii::t('PollsModule.widgets_views_stream', '<b>There are no polls yet!</b>'),
    'messageStreamEmptyCss' => ($contentContainer->canWrite()) ?
            'placeholder-empty-stream' :
            '',
));

```

## Create Content Form

### Create Form Widget

Create a Form Widget derived from [[humhub\modules\content\widgets\WallCreateContentForm]]

```php

namespace humhub\modules\polls\widgets;

class WallCreateForm extends \humhub\modules\content\widgets\WallCreateContentForm
{

    public $submitUrl = '/polls/poll/create';

    public function renderForm()
    {
        // Render your custom form here
        return $this->render('form', array());
    }

}

```

Create a view file for widget which contains module specific fields. All standard fields (e.g. visibility) are added automatically.

```php
<?php echo Html::textArea("question", "", array('id' => 'contentForm_question', 'class' => 'form-control autosize contentForm', 'rows' => '1', "tabindex" => "1", "placeholder" => Yii::t('PollsModule.widgets_views_pollForm', "Ask something..."))); ?>

<div class="contentForm_options">
    <?php echo Html::textArea("answersText", "", array('id' => "contentForm_answersText", 'rows' => '5', 'style' => 'height: auto !important;', "class" => "form-control contentForm", "tabindex" => "2", "placeholder" => Yii::t('PollsModule.widgets_views_pollForm', "Possible answers (one per line)"))); ?>
    <div class="checkbox">
        <label>
            <?php echo Html::checkbox("allowMultiple", "", array('id' => "contentForm_allowMultiple", 'class' => 'checkbox contentForm', "tabindex" => "4")); ?> <?php echo Yii::t('PollsModule.widgets_views_pollForm', 'Allow multiple answers per user?'); ?>
        </label>
    </div>

</div>
```

### Create Action

Create an action in your modules controller to receive form inputs.

All default tasks (e.g. access validation, ContentContainer assignment) are handled by [[humhub\modules\content\widgets\WallCreateContentForm::create]]


Example:

```php

public function actionCreate()
{
    $poll = new Poll();
    $poll->question = Yii::$app->request->post('question');
    $poll->answersText = Yii::$app->request->post('answersText');
    $poll->allow_multiple = Yii::$app->request->post('allowMultiple', 0);

    return \humhub\modules\polls\widgets\WallCreateForm::create($poll);
}


```

### Display Form

Place the Form widget above the Stream widget in your view.

e.g.

```php

<?php echo \humhub\modules\polls\widgets\WallCreateForm::widget(array('contentContainer' => $contentContainer)); ?>

```


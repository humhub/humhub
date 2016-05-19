# Streams / Walls

TBD

- Define Streaming/Wall

You can also implement Creanown Stream/Wall output for your module content only. 

Example Implementations:

- Tasks
- Polls

Of course your modules Content implementation needs to provides a WallEntry widget. See Content Section for more details.


## Create own Module Content Stream

### Implement StreamAction

Derived from [[humhub\modules\content\components\actions\ContentContainerStream]]

Example:

```php
<?php

namespace humhub\modules\polls\components;

use humhub\modules\content\components\actions\ContentContainerStream;
use humhub\modules\polls\models\Poll;

class StreamAction extends ContentContainerStream
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


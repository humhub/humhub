<?php

use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\content\widgets\WallCreateContentForm;
use humhub\modules\content\widgets\WallCreateContentFormFooter;
use humhub\modules\post\models\Post;
use humhub\modules\post\Module;
use humhub\widgets\form\ActiveForm;

/* @var WallCreateContentForm $wallCreateContentForm */
/* @var string $mentioningUrl */
/* @var ActiveForm $form */
/* @var Post $post */

$titleMode = Yii::$app->getModule('post')->getTitleMode();
?>

<?php if ($titleMode !== Module::TITLE_MODE_OFF): ?>
<div data-content-form-expand<?= $wallCreateContentForm->isModal ? '' : ' class="d-none"' ?>>
    <?= $form->field($post, 'title')->textInput([
        'class' => 'form-control contentForm',
        'placeholder' => Yii::t('PostModule.base', 'Title'),
        'maxlength' => true,
    ])->label(false) ?>
</div>
<?php endif; ?>

<?= $form->field($post, 'message')->widget(RichTextField::class, [
    'id' => 'contentForm_message' . ($wallCreateContentForm->isModal ? 'Modal' : ''),
    'form' => $form,
    'layout' => RichTextField::LAYOUT_BLOCK,
    'contextId' => 'post',
    'pluginOptions' => ['maxHeight' => '300px'],
    'placeholder' => Yii::t("PostModule.base", "What's on your mind?"),
    'name' => 'message',
    'disabled' => (property_exists(Yii::$app->controller, 'contentContainer') && Yii::$app->controller->contentContainer->isArchived()),
    'disabledText' => Yii::t("PostModule.base", "This space is archived."),
    'mentioningUrl' => $mentioningUrl,
])->label(false) ?>

<?= WallCreateContentFormFooter::widget([
    'contentContainer' => $post->content->container,
    'wallCreateContentForm' => $wallCreateContentForm,
]) ?>

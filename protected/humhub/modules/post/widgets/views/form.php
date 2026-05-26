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

// The message field uses a fixed (non-floating) toolbar. While the non-modal
// create form is still collapsed the toolbar is hidden and the editor shrinks to
// a single line; both are restored once the form expands on focus (the
// `contentForm-expanded` class, see content.form.js).
$css = '
    #contentFormBody .ProseMirror,
    #contentFormBodyModal .ProseMirror {
        min-height: 4.5em;
    }
    #contentFormBody:not(.contentForm-expanded) #contentForm_message .ProseMirror-menubar {
        display: none;
    }
    #contentFormBody:not(.contentForm-expanded) #contentForm_message .ProseMirror {
        min-height: 36px;
        border-top-left-radius: 4px !important;
        border-top-right-radius: 4px !important;
    }
';

if ($titleMode !== Module::TITLE_MODE_OFF) {
    // `.content-form-body .mb-3` resets all field margins, so restore a normal gap below the title.
    $css .= '
    .content-form-body [data-content-form-expand] {
        margin-bottom: 15px;
    }
    ';
}

$this->registerCss($css);
?>

<?php if ($titleMode !== Module::TITLE_MODE_OFF): ?>
<div data-content-form-expand<?= $wallCreateContentForm->isModal ? '' : ' style="display:none;"' ?>>
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

<?php

use humhub\modules\content\Module;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\file\widgets\UploadButton;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\comment\models\Comment;

/* @var $this View */
/* @var $objectModel string */
/* @var $objectId integer */
/* @var $model Comment */
/* @var $id string unique object id */
/* @var $isNestedComment boolean */
/** @var Module $contentModule */

$contentModule = Yii::$app->getModule('content');
$submitUrl = Url::to(['/comment/comment/post']);

$placeholder = ($isNestedComment)
    ? Yii::t('CommentModule.base', 'Write a new reply...')
    : Yii::t('CommentModule.base', 'Write a new comment...');

// Hide the comment form for sub comments until the button is clicked
$isHidden = ($objectModel === Comment::class);
?>

<div id="comment_create_form_<?= $id ?>" class="comment_create" data-ui-widget="comment.Form"
     style="<?php if ($isHidden): ?>display:none<?php endif; ?>">

    <hr>

    <?php $form = ActiveForm::begin(['action' => $submitUrl]) ?>

    <?= Html::hiddenInput('objectModel', $objectModel) ?>
    <?= Html::hiddenInput('objectId', $objectId) ?>

    <div class="comment-create-input-group">
        <?= $form->field($model, 'message')->widget(RichTextField::class, [
            'id' => 'newCommentForm_' . $id,
            'layout' => RichTextField::LAYOUT_INLINE,
            'pluginOptions' => ['maxHeight' => '300px'],
            'placeholder' => $placeholder,
            'events' => [
                'scroll-active' => 'comment.scrollActive',
                'scroll-inactive' => 'comment.scrollInactive'
            ]
        ])->label(false) ?>

        <div class="comment-buttons">
            <?= UploadButton::widget([
                'id' => 'comment_create_upload_' . $id,
                'tooltip' => Yii::t('ContentModule.base', 'Attach Files'),
                'options' => ['class' => 'main_comment_upload'],
                'progress' => '#comment_create_upload_progress_' . $id,
                'preview' => '#comment_create_upload_preview_' . $id,
                'dropZone' => '#comment_create_form_' . $id,
                'max' => $contentModule->maxAttachedFiles
            ]); ?>

            <?= Button::defaultType(Yii::t('CommentModule.base', 'Send'))
                ->cssClass('btn-comment-submit')
                ->action('submit', $submitUrl)->submit()->sm() ?>
        </div>
    </div>

    <div id="comment_create_upload_progress_<?= $id ?>" style="display:none;margin:10px 0px;"></div>

    <?= FilePreview::widget([
        'id' => 'comment_create_upload_preview_' . $id,
        'options' => ['style' => 'margin-top:10px'],
        'edit' => true
    ]) ?>

    <?php ActiveForm::end() ?>
</div>

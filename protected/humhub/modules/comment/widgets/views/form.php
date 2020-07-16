<?php

use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\file\widgets\UploadButton;
use humhub\modules\file\widgets\FilePreview;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $objectModel string */
/* @var $objectId integer */
/* @var $id string unique object id */
/* @var $isNestedComment boolean */

/** @var \humhub\modules\content\Module $contentModule */
$contentModule = Yii::$app->getModule('content');
$submitUrl = Url::to(['/comment/comment/post']);

$placeholder = ($isNestedComment) ? Yii::t('CommentModule.base', 'Write a new reply...') : Yii::t('CommentModule.base', 'Write a new comment...');

// Hide the comment form for sub comments until the button is clicked
$isHidden = ($objectModel === \humhub\modules\comment\models\Comment::class);
?>

<div id="comment_create_form_<?= $id; ?>" class="comment_create" data-ui-widget="comment.Form"
     style="<?php if ($isHidden): ?>display:none<?php endif; ?>">

    <hr>

    <?= Html::beginForm('#'); ?>
    <?= Html::hiddenInput('objectModel', $objectModel); ?>
    <?= Html::hiddenInput('objectId', $objectId); ?>

    <div class="comment-create-input-group">
        <?= RichTextField::widget([
            'id' => 'newCommentForm_' . $id,
            'layout' => RichTextField::LAYOUT_INLINE,
            'pluginOptions' => ['maxHeight' => '300px'],
            'placeholder' => $placeholder,
            'name' => 'message',
            'events' => [
                'scroll-active' => 'comment.scrollActive',
                'scroll-inactive' => 'comment.scrollInactive'
            ]
        ]); ?>

        <div class="comment-buttons">
            <?= UploadButton::widget([
                'id' => 'comment_create_upload_' . $id,
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
    ]); ?>

    <?= Html::endForm(); ?>
</div>

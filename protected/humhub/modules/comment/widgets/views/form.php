<?php

use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\file\widgets\UploadButton;
use humhub\modules\file\widgets\FilePreview;

/* @var $modelName string */
/* @var $modelId integer */

$submitUrl = Url::to(['/comment/comment/post']);

?>

<div id="comment_create_form_<?= $id; ?>" class="comment_create" data-ui-widget="comment.Form">

    <?= Html::beginForm('#'); ?>
    <?= Html::hiddenInput('contentModel', $modelName); ?>
    <?= Html::hiddenInput('contentId', $modelId); ?>

    <div class="comment-create-input-group">
        <?= RichTextField::widget([
            'id' => 'newCommentForm_' . $id,
            'layout' => RichTextField::LAYOUT_INLINE,
            'pluginOptions' => ['maxHeight' => '300px'],
            'placeholder' => Yii::t('CommentModule.widgets_views_form', 'Write a new comment...'),
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
                'dropZone' => '#comment_create_form_'.$id,
                'max' => Yii::$app->getModule('content')->maxAttachedFiles
            ]); ?>

            <?= Button::defaultType(Yii::t('CommentModule.widgets_views_form', 'Send'))
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
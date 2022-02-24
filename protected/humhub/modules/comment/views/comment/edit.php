<?php

use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\widgets\FileHandlerButtonDropdown;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\file\widgets\UploadButton;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;
use yii\helpers\Html;
use humhub\modules\content\widgets\richtext\RichTextField;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $objectModel string */
/* @var $objectId integer */
/* @var $comment \humhub\modules\comment\models\Comment */
/* @var $submitUrl string */
/* @var $fileHandlers BaseFileHandler[] */

/** @var \humhub\modules\content\Module $contentModule */
$contentModule = Yii::$app->getModule('content');

?>
<div class="content_edit input-container" id="comment_edit_<?= $comment->id; ?>">
    <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>
    <?= Html::hiddenInput('objectModel', $objectModel); ?>
    <?= Html::hiddenInput('objectId', $objectId); ?>

    <div class="comment-create-input-group">
        <?= $form->field($comment, 'message')->widget(RichTextField::class, [
            'id' => 'comment_input_' . $comment->id,
            'layout' => RichTextField::LAYOUT_INLINE,
            'pluginOptions' => ['maxHeight' => '300px'],
            'placeholder' => Yii::t('CommentModule.base', 'Edit your comment...'),
            'focus' => true,
            'events' => [
                'scroll-active' => 'comment.scrollActive',
                'scroll-inactive' => 'comment.scrollInactive'
            ]
        ])->label(false) ?>

        <div class="comment-buttons"><?php
            $uploadButton = UploadButton::widget([
                'id' => 'comment_upload_' . $comment->id,
                'model' => $comment,
                'tooltip' => Yii::t('ContentModule.base', 'Attach Files'),
                'dropZone' => '#comment_' . $comment->id,
                'preview' => '#comment_upload_preview_' . $comment->id,
                'progress' => '#comment_upload_progress_' . $comment->id,
                'max' => $contentModule->maxAttachedFiles,
                'cssButtonClass' => 'btn-sm btn-info',
            ]);
            echo FileHandlerButtonDropdown::widget([
                'primaryButton' => $uploadButton,
                'handlers' => $fileHandlers,
                'cssButtonClass' => 'btn-info btn-sm',
                'pullRight' => true,
            ]);
            echo Button::info()
                ->icon('send')
                ->cssClass('btn-comment-submit')->sm()
                ->action('editSubmit', $submitUrl)->submit();
        ?></div>
    </div>

    <div id="comment_upload_progress_<?= $comment->id ?>" style="display:none; margin:10px 0;"></div>

    <?= FilePreview::widget([
        'id' => 'comment_upload_preview_' . $comment->id,
        'options' => ['style' => 'margin-top:10px'],
        'model' => $comment,
        'edit' => true
    ]); ?>
    <?php ActiveForm::end(); ?>
</div>

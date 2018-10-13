<?php

use humhub\modules\file\widgets\FilePreview;
use humhub\modules\file\widgets\UploadButton;
use humhub\widgets\Button;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\content\widgets\richtext\RichTextField;

/* @var $contentModel string */
/* @var $contentId integer */
/* @var $comment \humhub\modules\comment\models\Comment */

$submitUrl = Url::to(['/comment/comment/edit', 'id' => $comment->id, 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id]);
?>

<div class="content_edit input-container" id="comment_edit_<?= $comment->id; ?>">
    <?php $form = ActiveForm::begin(); ?>
        <?= Html::hiddenInput('contentModel', $contentModel); ?>
        <?= Html::hiddenInput('contentId', $contentId); ?>

        <div class="comment-create-input-group">
            <?= $form->field($comment, 'message')->widget(RichTextField::class, [
                'id' => 'comment_input_'.$comment->id,
                'layout' => RichTextField::LAYOUT_INLINE,
                'pluginOptions' => ['maxHeight' => '300px'],
                'placeholder' => Yii::t('CommentModule.views_edit', 'Edit your comment...'),
                'focus' => true,
                'events' => [
                    'scroll-active' => 'comment.scrollActive',
                    'scroll-inactive' => 'comment.scrollInactive'
                ]
            ])->label(false) ?>

            <div class="comment-buttons">

                <?=  UploadButton::widget([
                    'id' => 'comment_upload_' . $comment->id,
                    'model' => $comment,
                    'dropZone' => '#comment_'.$comment->id,
                    'preview' => '#comment_upload_preview_'.$comment->id,
                    'progress' => '#comment_upload_progress_'.$comment->id,
                    'max' => Yii::$app->getModule('content')->maxAttachedFiles
                ]); ?>

                <?= Button::defaultType(Yii::t('base', 'Save'))->cssClass('btn-comment-submit')->action('editSubmit', $submitUrl)->submit()->sm() ?>

            </div>
        </div>

        <div id="comment_upload_progress_<?= $comment->id ?>" style="display:none; margin:10px 0;"></div>

        <?= FilePreview::widget([
            'id' => 'comment_upload_preview_'.$comment->id,
            'options' => ['style' => 'margin-top:10px'],
            'model' => $comment,
            'edit' => true
        ]); ?>
    <?php ActiveForm::end(); ?>
</div>
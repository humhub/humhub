<?php

use humhub\compat\CActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="content_edit input-container" id="comment_edit_<?= $comment->id; ?>">
    <?php $form = CActiveForm::begin(); ?>
    <?= Html::hiddenInput('contentModel', $contentModel); ?>
    <?= Html::hiddenInput('contentId', $contentId); ?>

    <?=
	humhub\widgets\RichtextField::widget([
        'id' => 'comment_input_'.$comment->id,
        'placeholder' => Yii::t('CommentModule.views_edit', 'Edit your comment...'),
        'model' => $comment,
        'attribute' => 'message'
    ]);
    ?>

    <div class="comment-buttons">

        <?=
        \humhub\modules\file\widgets\UploadButton::widget([
            'id' => 'comment_upload_' . $comment->id,
            'model' => $comment,
            'dropZone' => '#comment_'.$comment->id,
            'preview' => '#comment_upload_preview_'.$comment->id,
            'progress' => '#comment_upload_progress_'.$comment->id,
            'max' => Yii::$app->getModule('content')->maxAttachedFiles
        ]);
        ?>


        <a href="#" class="btn btn-default btn-sm btn-comment-submit"
            data-action-click="editSubmit"
            data-action-url="<?= Url::to(['/comment/comment/edit', 'id' => $comment->id, 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id]) ?>"
            data-action-submit
            data-ui-loader>
             <?= Yii::t('CommentModule.views_edit', 'Save') ?>
        </a>

    </div>

    <div id="comment_upload_progress_<?= $comment->id ?>" style="display:none; margin:10px 0;"></div>

    <?=
	\humhub\modules\file\widgets\FilePreview::widget([
        'id' => 'comment_upload_preview_'.$comment->id,
        'options' => ['style' => 'margin-top:10px'],
        'model' => $comment,
        'edit' => true
    ]);
    ?>

    <?php CActiveForm::end(); ?>
</div>
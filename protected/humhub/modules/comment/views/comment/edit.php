<?php

use humhub\compat\CActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="content_edit input-container" id="comment_edit_<?= $comment->id; ?>">
    <?php $form = CActiveForm::begin(); ?>
    <?= Html::hiddenInput('contentModel', $contentModel); ?>
    <?= Html::hiddenInput('contentId', $contentId); ?>
    <?= $form->textArea($comment, 'message', array('class' => 'form-control', 'id' => 'comment_input_' . $comment->id, 'placeholder' => Yii::t('CommentModule.views_edit', 'Edit your comment...'))); ?>

    <!-- create contenteditable div for HEditorWidget to place the data -->
    <div id="comment_input_<?= $comment->id; ?>_contenteditable" class="form-control atwho-input" contenteditable="true"><?= \humhub\widgets\RichText::widget(['text' => $comment->message, 'edit' => true]); ?></div>

    <!-- Modify textarea for mention input -->
    <?= \humhub\widgets\RichTextEditor::widget(array(
        'id' => 'comment_input_' . $comment->id,
        'inputContent' => $comment->message,
        'record' => $comment,
    ));
    ?>

    <div class="comment-buttons">

        <!-- Creates Uploading Button -->
        <?= \humhub\modules\file\widgets\FileUploadButton::widget(array(
            'uploaderId' => 'comment_upload_' . $comment->id,
            'fileListFieldName' => 'fileList',
            'object' => $comment
        ));
        ?>


        <?= \humhub\widgets\AjaxButton::widget([
            'label' => Yii::t('CommentModule.views_edit', 'Save'),
            'ajaxOptions' => [
                'type' => 'POST',
                'beforeSend' => new yii\web\JsExpression('function(html) { $("#comment_input_' . $comment->id . '_contenteditable").hide(); showLoader("' . $comment->id . '"); }'),
                'success' => new yii\web\JsExpression('function(html) { $("#comment_' . $comment->id . '").replaceWith(html); }'),
                'url' => Url::to(['/comment/comment/edit', 'id' => $comment->id, 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id]),
            ],
            'htmlOptions' => [
                'class' => 'btn btn-default btn-sm btn-comment-submit',
                'id' => 'comment_edit_post_' . $comment->id,
                'type' => 'submit'
            ],
        ]);
        ?>

    </div>

    <!-- Creates a list of already uploaded Files -->
    <?= \humhub\modules\file\widgets\FileUploadList::widget(array(
        'uploaderId' => 'comment_upload_' . $comment->id,
        'object' => $comment
    ));
    ?>

    <?php CActiveForm::end(); ?>
</div>

<script>

    // show laoder during ajax call
    function showLoader(comment_id) {
        $('#comment_edit_' + comment_id).html('<div class="loader" style="padding: 15px 0;"><div class="sk-spinner sk-spinner-three-bounce" style="margin:0;"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div>');
    }

</script>
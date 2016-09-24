<?php

use humhub\compat\CActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="content_edit input-container" id="comment_edit_<?php echo $comment->id; ?>">
    <?php $form = CActiveForm::begin(); ?>
    <?php echo Html::hiddenInput('contentModel', $contentModel); ?>
    <?php echo Html::hiddenInput('contentId', $contentId); ?>
    <?php echo $form->textArea($comment, 'message', array('class' => 'form-control', 'id' => 'comment_input_' . $comment->id, 'placeholder' => Yii::t('CommentModule.views_edit', 'Edit your comment...'))); ?>

    <!-- create contenteditable div for HEditorWidget to place the data -->
    <div id="comment_input_<?php echo $comment->id; ?>_contenteditable" class="form-control atwho-input"
         contenteditable="true"><?php echo \humhub\widgets\RichText::widget(['text' => $comment->message, 'edit' => true]); ?></div>


    <?php
    /* Modify textarea for mention input */
    echo \humhub\widgets\RichTextEditor::widget(array(
        'id' => 'comment_input_' . $comment->id,
        'inputContent' => $comment->message,
        'record' => $comment,
    ));
    ?>

    <div class="comment-buttons">

        <?php
        // Creates Uploading Button
        echo humhub\modules\file\widgets\FileUploadButton::widget(array(
            'uploaderId' => 'comment_upload_' . $comment->id,
            'fileListFieldName' => 'fileList',
            'object' => $comment
        ));
        ?>


        <?php
        echo \humhub\widgets\AjaxButton::widget([
            'label' => Yii::t('CommentModule.views_edit', 'Save'),
            'ajaxOptions' => [
                'type' => 'POST',
                'beforeSend' => new yii\web\JsExpression('function(html){  $("#comment_input_' . $comment->id . '_contenteditable").hide(); showLoader("' . $comment->id . '"); }'),
                'success' => new yii\web\JsExpression('function(html){  $("#comment_' . $comment->id . '").replaceWith(html); }'),
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

    <?php
    // Creates a list of already uploaded Files
    echo \humhub\modules\file\widgets\FileUploadList::widget(array(
        'uploaderId' => 'comment_upload_' . $comment->id,
        'object' => $comment
    ));
    ?>

    <?php CActiveForm::end(); ?>
</div>

<script type="text/javascript">

    // show laoder during ajax call
    function showLoader(comment_id) {
        $('#comment_edit_' + comment_id).html('<div class="loader" style="padding: 15px 0;"><div class="sk-spinner sk-spinner-three-bounce" style="margin:0;"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div>');
    }


</script>
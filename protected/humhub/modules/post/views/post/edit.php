<?php

use humhub\compat\CActiveForm;
?>
<div class="content_edit" id="post_edit_<?= $post->id; ?>">
    <?php $form = CActiveForm::begin(['id' => 'post-edit-form_' . $post->id]); ?>
    
    <!-- create contenteditable div for HEditorWidget to place the data -->
    <div id="post_input_<?= $post->id; ?>_contenteditable" class="form-control atwho-input" contenteditable="true">
        <?= \humhub\widgets\RichText::widget(['text' => $post->message, 'edit' => true]); ?>
    </div>

    <?= $form->field($post, 'message')->label(false)->textArea(array('class' => 'form-control', 'id' => 'post_input_' . $post->id, 'placeholder' => Yii::t('PostModule.views_edit', 'Edit your post...'))); ?>

    <?= \humhub\widgets\RichTextEditor::widget(['id' => 'post_input_' . $post->id, 'inputContent' => $post->message, 'record' => $post]); ?>

    <div class="comment-buttons">

        <?=
        // Creates Uploading Button
        humhub\modules\file\widgets\FileUploadButton::widget(array(
            'uploaderId' => 'post_upload_' . $post->id,
            'object' => $post
        ));
        ?>

        <?= \humhub\widgets\AjaxButton::widget([
            'label' => Yii::t('PostModule.views_edit', 'Save'),
            'ajaxOptions' => [
                'type' => 'POST',
                'beforeSend' => new yii\web\JsExpression('function(html) { $("#post_input_' . $post->id . '_contenteditable").hide(); showLoader("' . $post->id . '"); }'),
                'success' => new yii\web\JsExpression('function(html) { $(".wall_' . $post->getUniqueId() . '").replaceWith(html); }'),
                'statusCode' => ['400' => new yii\web\JsExpression('function(xhr) { $("#post_edit_'. $post->id.'").replaceWith(xhr.responseText); }')],
                'url' => $post->content->container->createUrl('/post/post/edit', ['id' => $post->id]),
            ],
            'htmlOptions' => [
                'class' => 'btn btn-default btn-sm btn-comment-submit',
                'id' => 'post_edit_post_' . $post->id,
                'type' => 'submit'
            ]
        ]);
        ?>

    </div>

        <?=
        // Creates a list of already uploaded Files
        \humhub\modules\file\widgets\FileUploadList::widget(array(
            'uploaderId' => 'post_upload_' . $post->id,
            'object' => $post
        ));
        ?>

    <?php CActiveForm::end(); ?>
</div>

<script>

    // show loader during ajax call
    function showLoader(post_id) {
        $('#post_edit_' + post_id).html('<div class="loader" style="padding: 15px 0;"><div class="sk-spinner sk-spinner-three-bounce" style="margin:0;"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div>');
    }

</script>
<?php

use humhub\compat\CActiveForm;


?>
<div class="content content_edit" id="post_edit_<?php echo $post->id; ?>">
    <?php $form = CActiveForm::begin(['id' => 'post-edit-form_' . $post->id]); ?>
    
    <!-- create contenteditable div for HEditorWidget to place the data -->
    <div id="post_input_<?php echo $post->id; ?>_contenteditable" class="form-control atwho-input"
         contenteditable="true"><?php echo \humhub\widgets\RichText::widget(['text' => $post->message, 'edit' => true]); ?></div>

    <?php echo $form->field($post, 'message')->label(false)->textarea(array('class' => 'form-control', 'id' => 'post_input_' . $post->id, 'placeholder' => Yii::t('PostModule.views_edit', 'Edit your post...'))); ?>

    <?= \humhub\widgets\RichTextEditor::widget(['id' => 'post_input_' . $post->id, 'inputContent' => $post->message, 'record' => $post]); ?>

    <div class="comment-buttons">

    <?php
    // Creates Uploading Button
    echo humhub\modules\file\widgets\FileUploadButton::widget(array(
        'uploaderId' => 'post_upload_' . $post->id,
        'object' => $post
    ));
    ?>
        <button type="submit" class="btn btn-default btn-sm btn-comment-submit" data-ui-loader data-action-click="editSubmit" data-action-url="<?= $post->content->container->createUrl('/post/post/edit', ['id' => $post->id]) ?>">
            <?= Yii::t('PostModule.views_edit', 'Save') ?>
        </button>
       

    </div>

        <?php
        // Creates a list of already uploaded Files
        echo \humhub\modules\file\widgets\FileUploadList::widget(array(
            'uploaderId' => 'post_upload_' . $post->id,
            'object' => $post
        ));
        ?>


    <?php CActiveForm::end(); ?>
</div>
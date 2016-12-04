<?php

use humhub\compat\CActiveForm;
use yii\helpers\Html;

?>
<div class="content content_edit" id="post_edit_<?php echo $post->id; ?>">
    <?php $form = CActiveForm::begin(['id' => 'post-edit-form_' . $post->id]); ?>
    
    <!-- create contenteditable div for HEditorWidget to place the data -->
    <div id="post_input_<?php echo $post->id; ?>_contenteditable" class="form-control atwho-input" contenteditable="true">
        <?php echo \humhub\widgets\RichText::widget(['text' => $post->message, 'edit' => true]); ?>
    </div>

    <?= Html::activeTextarea($post, 'message', ['class' => 'form-control', 'id' => 'post_input_' . $post->id, 'placeholder' => Yii::t('PostModule.views_edit', 'Edit your post...')]) ?>

    <?= \humhub\widgets\RichTextEditor::widget(['id' => 'post_input_' . $post->id, 'inputContent' => $post->message, 'record' => $post]); ?>

    <div class="comment-buttons">

        <?= \humhub\modules\file\widgets\UploadButton::widget([
            'id' => 'post_upload_' . $post->id,
            'model' => $post,
            'dropZone' => '#post_edit_'.$post->id.':parent',
            'preview' => '#post_upload_preview_'.$post->id,
            'progress' => '#post_upload_progress_'.$post->id
        ]) ?>

        <!-- editSubmit action of surrounding StreamEntry component -->
        <button type="submit" class="btn btn-default btn-sm btn-comment-submit" data-ui-loader data-action-click="editSubmit" data-action-url="<?= $post->content->container->createUrl('/post/post/edit', ['id' => $post->id]) ?>">
            <?= Yii::t('PostModule.views_edit', 'Save') ?>
        </button>

    </div>
    
    <div id="post_upload_progress_<?= $post->id ?>" style="display:none;margin:10px 0px;"></div>
    
    <?= \humhub\modules\file\widgets\FilePreview::widget([
        'id' => 'post_upload_preview_'.$post->id,
        'options' => ['style' => 'margin-top:10px'],
        'model' => $post,
        'edit' => true
    ]) ?>
    
    <?php CActiveForm::end(); ?>
</div>
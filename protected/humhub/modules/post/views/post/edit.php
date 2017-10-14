<?php

use humhub\compat\CActiveForm;
use humhub\modules\file\widgets\FilePreview;
use humhub\widgets\Button;
use humhub\widgets\RichtextField;

$submitUrl = $post->content->container->createUrl('/post/post/edit', ['id' => $post->id]);

?>
<div class="content content_edit" id="post_edit_<?php echo $post->id; ?>">
    <?php $form = CActiveForm::begin(['id' => 'post-edit-form_' . $post->id]); ?>

    <!-- create contenteditable div for HEditorWidget to place the data -->
    <?= RichtextField::widget([
        'id' => 'post_input_'. $post->id,
        'placeholder' => Yii::t('PostModule.views_edit', 'Edit your post...'),
        'model' => $post,
        'attribute' => 'message'
    ]); ?>

    <div class="comment-buttons">

        <?=
        \humhub\modules\file\widgets\UploadButton::widget([
            'id' => 'post_upload_' . $post->id,
            'model' => $post,
            'dropZone' => '#post_edit_' . $post->id . ':parent',
            'preview' => '#post_upload_preview_' . $post->id,
            'progress' => '#post_upload_progress_' . $post->id,
            'max' => Yii::$app->getModule('content')->maxAttachedFiles
        ])
        ?>

        <!-- editSubmit action of surrounding StreamEntry component -->
        <?= Button::defaultType(Yii::t('base', 'Save'))->action('editSubmit', $submitUrl)->submit()->cssClass(' btn-comment-submit')->sm(); ?>

    </div>

    <div id="post_upload_progress_<?= $post->id ?>" style="display:none;margin:10px 0px;"></div>

    <?=
    FilePreview::widget([
        'id' => 'post_upload_preview_' . $post->id,
        'options' => ['style' => 'margin-top:10px'],
        'model' => $post,
        'edit' => true
    ])
    ?>

<?php CActiveForm::end(); ?>
</div>
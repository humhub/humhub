<?php

use humhub\modules\file\widgets\FilePreview;
use humhub\modules\file\widgets\UploadButton;
use humhub\modules\file\widgets\UploadProgress;
use humhub\widgets\Button;
use humhub\modules\content\widgets\richtext\RichTextField;
use yii\bootstrap\ActiveForm;

/* @var  $post \humhub\modules\post\models\Post */
/* @var  $from string */
/* @var  $submitUrl string */

$submitParams = ['id' => $post->id];
if (! empty($from)) {
    $submitParams['from'] = $from;
}
$submitUrl = $post->content->container->createUrl('/post/post/edit', $submitParams);

?>
<div class="content content_edit" id="post_edit_<?= $post->id; ?>">
    <?php $form = ActiveForm::begin(['id' => 'post-edit-form_' . $post->id]); ?>

        <div class="post-richtext-input-group">
            <?= $form->field($post, 'message')->widget(RichTextField::class, [
                'id' => 'post_input_'. $post->id,
                'layout' => RichTextField::LAYOUT_INLINE,
                'pluginOptions' => ['maxHeight' => '300px'],
                'placeholder' => Yii::t('PostModule.base', 'Edit your post...')
            ])->label(false) ?>

            <div class="comment-buttons">

                <?= UploadButton::widget([
                    'id' => 'post_upload_' . $post->id,
                    'model' => $post,
                    'dropZone' => '#post_edit_' . $post->id . ':parent',
                    'preview' => '#post_upload_preview_' . $post->id,
                    'progress' => '#post_upload_progress_' . $post->id,
                    'max' => Yii::$app->getModule('content')->maxAttachedFiles
                ]) ?>

                <?= Button::defaultType(Yii::t('base', 'Save'))->action('editSubmit', $submitUrl)->submit()->cssClass(' btn-comment-submit')->sm(); ?>

            </div>
        </div>

        <?= UploadProgress::widget(['id' => 'post_upload_progress_'.$post->id])?>

        <?= FilePreview::widget([
            'id' => 'post_upload_preview_' . $post->id,
            'options' => ['style' => 'margin-top:10px'],
            'model' => $post,
            'edit' => true
        ]) ?>

    <?php ActiveForm::end(); ?>
</div>
<?php

use humhub\libs\Html;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\widgets\FileHandlerButtonDropdown;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\file\widgets\UploadButton;
use humhub\modules\file\widgets\UploadProgress;
use humhub\modules\post\models\forms\PostEditForm;
use humhub\widgets\Button;
use yii\bootstrap\ActiveForm;

/* @var $model PostEditForm */
/* @var $submitUrl string */
/* @var $fileHandlers BaseFileHandler[] */
/* @var $viewContext string */
?>
<div class="content content_edit" id="post_edit_<?= $model->post->id; ?>">
    <?php $form = ActiveForm::begin(['id' => 'post-edit-form_' . $model->post->id]); ?>

    <div class="content-create-input-group">
        <?= $form->field($model->post, 'message')->widget(RichTextField::class, [
            'id' => 'post_input_' . $model->post->id,
            'layout' => RichTextField::LAYOUT_INLINE,
            'focus' => true,
            'pluginOptions' => ['maxHeight' => '300px'],
            'placeholder' => Yii::t('PostModule.base', 'Edit your post...'),
        ])->label(false) ?>

        <div class="upload-buttons">
            <?= FileHandlerButtonDropdown::widget([
                'primaryButton' => UploadButton::widget([
                    'id' => 'post_upload_' . $model->post->id,
                    'tooltip' => Yii::t('ContentModule.base', 'Attach Files'),
                    'model' => $model,
                    'dropZone' => '#post_edit_' . $model->post->id . ':parent',
                    'preview' => '#post_upload_preview_' . $model->post->id,
                    'progress' => '#post_upload_progress_' . $model->post->id,
                    'max' => Yii::$app->getModule('content')->maxAttachedFiles,
                    'cssButtonClass' => 'btn-sm btn-default',
                ]),
                'handlers' => $fileHandlers,
                'cssButtonClass' => 'btn-sm btn-default',
                'pullRight' => true,
            ]) ?>
            <?= Button::info()
                ->icon('send')
                ->action('editSubmit', $submitUrl)
                ->cssClass(' btn-comment-submit')->sm()
                ->submit() ?>
        </div>
    </div>

    <?= UploadProgress::widget(['id' => 'post_upload_progress_' . $model->post->id]) ?>

    <?= FilePreview::widget([
        'id' => 'post_upload_preview_' . $model->post->id,
        'options' => ['style' => 'margin-top:10px'],
        'model' => $model->post,
        'edit' => true,
    ]) ?>

    <?= Html::hiddenInput('viewContext', $viewContext) ?>

    <?php ActiveForm::end(); ?>
</div>

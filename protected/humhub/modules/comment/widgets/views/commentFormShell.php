<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\widgets\CommentFormShell;
use humhub\modules\content\Module as ContentModule;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\widgets\FileHandlerButtonDropdown;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\file\widgets\UploadButton;
use humhub\widgets\form\ActiveForm;

/* @var $this View */
/* @var $model Comment */
/* @var $contentModule ContentModule */
/* @var $mentioningUrl string */
/* @var $fileHandlers BaseFileHandler[] */

// Every id below is built from this token - see CommentFormShell's class docblock for the
// full contract the client-side clone/replace step relies on.
$token = CommentFormShell::TOKEN;
?>
<div id="comment_create_form_<?= $token ?>" class="comment_create content_create">
    <hr>

    <?php // No real submit target: the island intercepts and JSON-posts the form itself
    // (see CommentForm.vue) - a static action avoids Yii falling back to the current
    // request URL, which is unavailable outside a full web request (e.g. unit tests).
    // `csrf => false` (an option `Html::beginForm()` reads directly, see
    // `yii\widgets\ActiveForm::run()`) skips the hidden `_csrf` input: this shell is
    // rendered once and cloned per instance (see LegacyFormWrapper.vue), so a baked-in
    // token would go stale for every clone anyway - nothing in this component tree ever
    // reads it (CommentForm.vue's onSubmit() only reads the editor value/file guids and
    // posts via `client.post()`; Yii's own `yii.js` ajax setup attaches a fresh
    // `X-CSRF-Token` header from the live `meta[name=csrf-token]` tag to every jQuery
    // ajax request app-wide), so the hidden input would only ever be dead weight. ?>
    <?php $form = ActiveForm::begin([
        'action' => '#',
        'options' => ['id' => 'w' . $token, 'csrf' => false],
        'acknowledge' => true,
    ]) ?>

    <div class="richtext-create-input-group input-group">
        <?= $form->field($model, 'message')->widget(RichTextField::class, [
            'id' => 'newCommentForm_' . $token,
            'form' => $form,
            'layout' => RichTextField::LAYOUT_INLINE,
            'pluginOptions' => ['maxHeight' => '300px'],
            'mentioningUrl' => $mentioningUrl,
            'placeholder' => Yii::t('CommentModule.base', 'Write a new comment...'),
            'events' => [
                'scroll-active' => 'comment.scrollActive',
                'scroll-inactive' => 'comment.scrollInactive',
            ],
        ])->label(false) ?>

        <div class="richtext-create-buttons">
            <?php $uploadButton = UploadButton::widget([
                'id' => 'comment_create_upload_' . $token,
                'model' => $model,
                'attribute' => 'fileList',
                'tooltip' => Yii::t('ContentModule.base', 'Attach Files'),
                'options' => ['class' => 'main_comment_upload'],
                'progress' => '#comment_create_upload_progress_' . $token,
                'preview' => '#comment_create_upload_preview_' . $token,
                'dropZone' => '#comment_create_form_' . $token,
                'max' => $contentModule->maxAttachedFiles,
            ]) ?>
            <?= FileHandlerButtonDropdown::widget([
                'primaryButton' => $uploadButton,
                'handlers' => $fileHandlers,
                'cssClass' => 'btn-group btn-group-sm',
                'cssButtonClass' => 'btn-light',
                'pullRight' => true,
            ]) ?>
        </div>
    </div>

    <div id="comment_create_upload_progress_<?= $token ?>" style="display:none;margin:10px 0px;"></div>

    <?= FilePreview::widget([
        'id' => 'comment_create_upload_preview_' . $token,
        'options' => ['style' => 'margin-top:10px'],
        'edit' => true,
    ]) ?>

    <?php ActiveForm::end() ?>
</div>

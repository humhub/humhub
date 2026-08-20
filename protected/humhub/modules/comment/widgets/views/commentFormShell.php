<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\Module as ContentModule;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\widgets\FileHandlerButtonDropdown;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\file\widgets\UploadButton;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\VueFormShell;

/* @var $this View */
/* @var $model Comment */
/* @var $contentModule ContentModule */
/* @var $mentioningUrl string */
/* @var $fileHandlers BaseFileHandler[] */

// The outer wrapper (this div + its `dropZone`-target id) is comment-specific markup around
// the generic ActiveForm shell VueFormShell itself owns below - see that class's docblock for
// the `__VUEFORM__` token contract every id here (via VueFormShell::id()) participates in.
$dropZoneId = VueFormShell::id('comment_create_form');
?>
<div id="<?= $dropZoneId ?>" class="comment_create content_create">
    <hr>

    <?= VueFormShell::widget([
        'content' => function (ActiveForm $form) use ($model, $contentModule, $mentioningUrl, $fileHandlers, $dropZoneId) {
            ob_start(); ?>
            <div class="richtext-create-input-group input-group">
                <?= $form->field($model, 'message')->widget(RichTextField::class, [
                    'id' => VueFormShell::id('newCommentForm'),
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
                        'id' => VueFormShell::id('comment_create_upload'),
                        'model' => $model,
                        'attribute' => 'fileList',
                        'tooltip' => Yii::t('ContentModule.base', 'Attach Files'),
                        // `vueform-upload` is the generic convention LegacyFormWrapper.vue's
                        // UPLOAD_SELECTOR queries (see its class docblock) - no SCSS targets
                        // the former comment-only `main_comment_upload` class, so this is a
                        // clean rename, not an addition.
                        'options' => ['class' => 'vueform-upload'],
                        'progress' => '#' . VueFormShell::id('comment_create_upload_progress'),
                        'preview' => '#' . VueFormShell::id('comment_create_upload_preview'),
                        'dropZone' => '#' . $dropZoneId,
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

            <div id="<?= VueFormShell::id('comment_create_upload_progress') ?>" style="display:none;margin:10px 0px;"></div>

            <?= FilePreview::widget([
                'id' => VueFormShell::id('comment_create_upload_preview'),
                'options' => ['style' => 'margin-top:10px'],
                'edit' => true,
            ]) ?>
            <?php
            return ob_get_clean();
        },
    ]) ?>
</div>

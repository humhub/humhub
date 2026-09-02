<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\VueFormShell;

/* @var $this View */
/* @var $model Comment */
/* @var $mentioningUrl string */

// The outer wrapper (this div + its `dropZone`-target id) is comment-specific markup around
// the generic ActiveForm shell VueFormShell itself owns below - see that class's docblock for
// the `__VUEFORM__` token contract every id here (via VueFormShell::id()) participates in.
// `CommentForm.vue` also uses this element as the drop/paste zone of its upload field, the
// same area the legacy upload widget's `dropZone` option pointed at.
$dropZoneId = VueFormShell::id('comment_create_form');
?>
<div id="<?= $dropZoneId ?>" class="comment_create content_create">
    <hr>

    <?= VueFormShell::widget([
        'content' => function (ActiveForm $form) use ($model, $mentioningUrl) {
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

                <?php
                // The button row the island teleports into: the upload field's trigger
                // (`UploadField`, which replaced the former server-rendered UploadButton +
                // FileHandlerButtonDropdown + UploadProgress + FilePreview composition) and
                // the submit button - see CommentForm.vue.
                ?>
                <div class="richtext-create-buttons"></div>
            </div>
            <?php
            return ob_get_clean();
        },
    ]) ?>
</div>

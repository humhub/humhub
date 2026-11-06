<?php

use humhub\helpers\Html;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use yii\base\Model;

/**
 * One image upload field with preview, upload and delete buttons
 *
 * @var $form ActiveForm
 * @var $model Model
 * @var $attribute string the form attribute, also the name of the file input
 * @var $key string the id part of the elements, as used by humhub.admin.js
 * @var $url string the current image, empty if none
 * @var $exists bool
 * @var $changeAction string
 * @var $deleteAction string
 * @var $deleteImage string the image parameter of `/admin/appearance/delete-image`
 */
?>
<div class="bg-light p-3 mt-2">
    <?= $form->field($model, $attribute)->fileInput([
        'id' => 'admin-' . $key . '-file-upload',
        'data-action-change' => $changeAction,
        'class' => 'd-none',
        'name' => $attribute . '[]',
    ]) ?>
    <div class="image-upload-container" id="<?= $key ?>-upload" style="min-height:40px">
        <?= Html::tag('img', '', [
            'src' => $url ?: null,
            'alt' => $model->getAttributeLabel($attribute),
            'id' => $key . '-image',
            'class' => 'rounded' . ($url === '' ? ' d-none' : ''),
            'style' => 'max-height:40px',
        ]) ?>

        <div class="image-upload-buttons" id="<?= $key ?>-upload-buttons">
            <?= Button::accent()
                ->id('admin-' . $key . '-upload-button')
                ->icon('cloud-upload')
                ->options(['aria-label' => Yii::t('AdminModule.settings', 'Upload')])
                ->sm()
                ->loader(false) ?>

            <?= Button::danger()
                ->id('admin-delete-' . $key . '-image')
                ->icon('remove')
                ->action($deleteAction, ['/admin/appearance/delete-image', 'image' => $deleteImage])
                ->options(['aria-label' => Yii::t('AdminModule.settings', 'Remove')])
                ->cssClass($exists ? '' : 'd-none')
                ->sm()
                ->loader(false) ?>
        </div>
    </div>
</div>

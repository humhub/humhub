<?php

use humhub\components\View;
use humhub\modules\file\widgets\Upload;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\modal\ModalButton;

/* @var $this View */
/* @var $upload Upload */
/* @var $cropUrl string */
/* @var $deleteUrl string */
/* @var $hasImage string */
/* @var $confirmBody string */
/* @var $dropZone string */

if (!isset($dropZone)) {
    $dropZone = null;
}

if (!isset($confirmBody)) {
    $confirmBody = null;
}
?>

<div class="image-upload-buttons d-none">

    <?= $upload->button([
        'cssButtonClass' => 'btn btn-info btn-sm profile-image-upload',
        'tooltip' => false,
        'dropZone' => $dropZone,
        'options' => ['class' => 'profile-upload-input']]) ?>

    <?= ModalButton::info()
        ->sm()
        ->load($cropUrl)->icon('edit')
        ->cssClass('profile-image-edit profile-image-crop' . ($hasImage ? '' : ' d-none')) ?>

    <?= Button::danger()
        ->icon('remove')
        ->action('delete', $deleteUrl)
        ->sm()
        ->loader(false)
        ->cssClass('profile-image-edit profile-image-delete' . ($hasImage ? '' : ' d-none'))
        ->confirm(
            Yii::t('SpaceModule.base', '<strong>Confirm</strong> image deletion'),
            $confirmBody,
            Yii::t('SpaceModule.base', 'Delete'),
            Yii::t('SpaceModule.base', 'Cancel'),
        ) ?>
</div>

<?php

use humhub\modules\file\widgets\Upload;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalConfirm;

/* @var $this \humhub\components\View */
/* @var $upload Upload */
/* @var $cropUrl string */
/* @var $deleteUrl string */
/* @var $hasImage string */
/* @var $confirmBody string */
/* @var $dropZone string */

$editButtonStyle = $hasImage ? '' : 'display: none;';

if (!isset($dropZone)) {
    $dropZone = null;
}

if (!isset($confirmBody)) {
    $confirmBody = null;
}
?>

<div class="image-upload-buttons">

    <?= $upload->button([
        'cssButtonClass' => 'btn btn-info btn-sm profile-image-upload',
        'tooltip' => false,
        'dropZone' => $dropZone,
        'options' => ['class' => 'profile-upload-input']]) ?>

    <?= ModalButton::info()->style($editButtonStyle)->sm()
        ->load($cropUrl)->icon('edit')
        ->cssClass('profile-image-edit profile-image-crop') ?>

    <?= Button::danger()
        ->icon('times')
        ->action('delete', $deleteUrl)
        ->style($editButtonStyle)->sm()
        ->loader(false)
        ->cssClass('profile-image-edit profile-image-delete')
        ->confirm(
            Yii::t('SpaceModule.base', '<strong>Confirm</strong> image deletion'),
            $confirmBody,
            Yii::t('SpaceModule.base', 'Delete'),
            Yii::t('SpaceModule.base', 'Cancel')) ?>
</div>

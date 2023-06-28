<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

use humhub\modules\file\widgets\Upload;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $upload Upload */
/* @var $cropUrl string */
/* @var $deleteUrl string */
/* @var $hasImage string */
/* @var $confirmBody string */
/* @var $dropZone string */

$editButtonStyle = $hasImage ? '' :'';// 'display: none;';

if (!isset($dropZone)) {
    $dropZone = null;
}

if (!isset($confirmBody)) {
    $confirmBody = null;
}
?>

<div class="attached-image-upload-buttons image-upload-buttons">

    <?= $upload->button([
        'cssButtonClass' => 'btn btn-info btn-sm profile-image-upload',
        'tooltip' => false,
        'dropZone' => $dropZone,
        'options' => ['class' => 'profile-upload-input']]) ?>

    <?= ModalButton::info()->style($editButtonStyle)->sm()
        ->load($cropUrl)
        ->icon('edit')
        ->cssClass('attached-image-edit attached-image-crop profile-image-edit profile-image-crop') ?>

    <?= Button::danger()
        ->icon('remove')
        ->action('delete', $deleteUrl)
        ->style($editButtonStyle)->sm()
        ->loader(false)
        ->cssClass('attached-image-edit attached-image-delete profile-image-edit profile-image-delete')
        ->confirm(
            Yii::t('FileModule.image', '<strong>Confirm</strong> image deletion'),
            $confirmBody,
            Yii::t('FileModule.image', 'Delete'),
            Yii::t('FileModule.image', 'Cancel')) ?>
</div>

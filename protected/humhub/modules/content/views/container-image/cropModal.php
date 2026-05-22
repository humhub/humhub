<?php

use humhub\components\assets\AssetImage;
use humhub\components\View;
use humhub\helpers\Html;
use humhub\models\forms\CropProfileImage;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\controllers\ContainerImageController;
use humhub\modules\space\models\Space;
use humhub\modules\ui\widgets\CropImage;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Json;

/* @var $this View */
/* @var $assetImage AssetImage */
/* @var $model CropProfileImage */
/* @var $imageType string */
/* @var $container ContentContainerController */
/* @var $submitUrl string */


$aspectRatio = $assetImage->defaultOptions['width'] / $assetImage->defaultOptions['height'];

if ($imageType === ContainerImageController::TYPE_PROFILE_BANNER_IMAGE) {
    $model->aspectRatio = ($container instanceof Space)
        ? $this->theme->variable('space-profile-banner-ratio', $aspectRatio)
        : $this->theme->variable('user-profile-banner-ratio', $aspectRatio);

    $cropSelect = ($container instanceof Space)
        ? $this->theme->variable('space-profile-banner-crop', '0, 0, ' . $assetImage->defaultOptions['width'] . ', ' . $assetImage->defaultOptions['height'])
        : $this->theme->variable('user-profile-banner-crop', '0, 0, ' . $assetImage->defaultOptions['width'] . ', ' . $assetImage->defaultOptions['height']);
} else {
    $model->aspectRatio = ($container instanceof Space)
        ? $this->theme->variable('space-profile-image-ratio', $aspectRatio)
        : $this->theme->variable('user-profile-image-ratio', $aspectRatio);

    $cropSelect = ($container instanceof Space)
        ? $this->theme->variable('space-profile-image-crop', '0, 0, ' . $assetImage->defaultOptions['width'] . ', ' . $assetImage->defaultOptions['height'])
        : $this->theme->variable('user-profile-image-crop', '0, 0, ' . $assetImage->defaultOptions['width'] . ', ' . $assetImage->defaultOptions['height']);
}

$model->cropSetSelect = Json::decode('[' . $cropSelect . ']');

?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('SpaceModule.views_admin_cropImage', '<strong>Modify</strong> image'),
    'footer' => ModalButton::cancel() . ' ' . ModalButton::save()->submit(),
    'id' => 'profile-image-crop-modal',
    'form' => ['id' => 'profile-image-crop-modal-form'],
]) ?>

    <style>
        /* Dirty Workaround against bootstrap and jcrop */
        #profile-image-crop-modal img {
            max-width: none;
        }

        #profile-image-crop-modal .jcrop-keymgr, #profile-image-crop-modal label {
            opacity: 0
        }

        #cropimage > .jcrop-holder {
            left: 50%;
            transform: translateX(-50%);
        }
    </style>

    <?= $form->errorSummary($model); ?>
    <?= $form->field($model, 'cropX')->hiddenInput(['id' => 'cropX'])->label(false) ?>
    <?= $form->field($model, 'cropY')->hiddenInput(['id' => 'cropY'])->label(false) ?>
    <?= $form->field($model, 'cropW')->hiddenInput(['id' => 'cropW'])->label(false) ?>
    <?= $form->field($model, 'cropH')->hiddenInput(['id' => 'cropH'])->label(false) ?>

    <div id="cropimage" style="overflow:hidden;">
        <?= Html::img($assetImage->getUrl([]), ['id' => 'crop-profile-image']) ?>

        <?= CropImage::widget(['selector' => '#crop-profile-image',
            'pluginOptions' => $model->getPluginOptions(),]); ?>
    </div>

<?php Modal::endFormDialog() ?>

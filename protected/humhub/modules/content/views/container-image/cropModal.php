<?php

use humhub\helpers\Html;
use humhub\libs\ProfileBannerImage;
use humhub\libs\ProfileImage;
use humhub\models\forms\CropProfileImage;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;
use humhub\modules\ui\view\components\View;
use humhub\modules\ui\widgets\CropImage;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Json;

/* @var $this View */
/* @var $profileImage ProfileImage */
/* @var $model CropProfileImage */
/* @var $container ContentContainerController */
/* @var $submitUrl string */

if ($profileImage instanceof ProfileBannerImage) {
    $model->aspectRatio = ($container instanceof Space)
        ? $this->theme->variable('space-profile-banner-ratio', $profileImage->getAspectRatio())
        : $this->theme->variable('user-profile-banner-ratio', $profileImage->getAspectRatio());

    $cropSelect = ($container instanceof Space)
        ? $this->theme->variable('space-profile-banner-crop', '0, 0, ' . $profileImage->width() . ', ' . $profileImage->height())
        : $this->theme->variable('user-profile-banner-crop', '0, 0, ' . $profileImage->width() . ', ' . $profileImage->height());
} else {
    $model->aspectRatio = ($container instanceof Space)
        ? $this->theme->variable('space-profile-image-ratio', $profileImage->getAspectRatio())
        : $this->theme->variable('user-profile-image-ratio', $profileImage->getAspectRatio());

    $cropSelect = ($container instanceof Space)
        ? $this->theme->variable('space-profile-image-crop', '0, 0, ' . $profileImage->width() . ', ' . $profileImage->height())
        : $this->theme->variable('user-profile-image-crop', '0, 0, ' . $profileImage->width() . ', ' . $profileImage->height());
}

$model->cropSetSelect = Json::decode('[' . $cropSelect . ']');

?>

<?php $form = ActiveForm::begin(['id' => 'profile-image-crop-modal-form']); ?>
<?= $form->errorSummary($model); ?>
<?= $form->field($model, 'cropX')->hiddenInput(['id' => 'cropX'])->label(false) ?>
<?= $form->field($model, 'cropY')->hiddenInput(['id' => 'cropY'])->label(false) ?>
<?= $form->field($model, 'cropW')->hiddenInput(['id' => 'cropW'])->label(false) ?>
<?= $form->field($model, 'cropH')->hiddenInput(['id' => 'cropH'])->label(false) ?>

<?php Modal::beginDialog([
    'footer' => ModalButton::cancel() . ' ' . ModalButton::submitModal(),
    'id' => 'profile-image-crop-modal',
    'header' => Yii::t('SpaceModule.views_admin_cropImage', '<strong>Modify</strong> image'),
    'size' => Modal::SIZE_SMALL,
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

<div id="cropimage" style="overflow:hidden;">
    <?= Html::img($profileImage->getUrl('_org'), ['id' => 'crop-profile-image']) ?>

    <?= CropImage::widget(['selector' => '#crop-profile-image',
        'pluginOptions' => $model->getPluginOptions(),]); ?>
</div>

<?php Modal::endDialog() ?>

<?php ActiveForm::end(); ?>

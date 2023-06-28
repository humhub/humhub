<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use raoul2000\jcrop\JCropWidget;
use yii\helpers\Html;
use yii\helpers\Json;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $attachedImage \humhub\modules\file\models\AttachedImage */
/* @var $model \humhub\models\forms\CropProfileImage */
/* @var $container \humhub\modules\content\components\ContentContainerController */
/* @var $submitUrl string */

$model->aspectRatio  = $attachedImage->getAspectRatioThemed($this->theme);
$cropSelect  = $attachedImage->getCropAreaThemed($this->theme);
$model->cropSetSelect = Json::decode('['.$cropSelect.']');

?>

<?php ModalDialog::begin([
    'id' => 'profile-image-crop-modal',
    'header' => Yii::t('SpaceModule.views_admin_cropImage', '<strong>Modify</strong> image'),
    'animation' => 'fadeIn',
    'size' => 'small']) ?>

    <?php $form = ActiveForm::begin(['id' => 'profile-image-crop-modal-form']); ?>
        <?= $form->errorSummary($model); ?>
        <?= $form->field($model, 'cropX')->hiddenInput(['id' => 'cropX'])->label(false) ?>
        <?= $form->field($model, 'cropY')->hiddenInput(['id' => 'cropY'])->label(false) ?>
        <?= $form->field($model, 'cropW')->hiddenInput(['id' => 'cropW'])->label(false) ?>
        <?= $form->field($model, 'cropH')->hiddenInput(['id' => 'cropH'])->label(false) ?>

        <div class="modal-body">
            <style>
                /* Dirty Workaround against bootstrap and jcrop */
                #profile-image-crop-modal img {
                    max-width: none;
                }

                #profile-image-crop-modal .jcrop-keymgr, #profile-image-crop-modal label {
                    opacity:0
                }
            </style>

            <div id="cropimage" style="overflow:hidden;">
                <?= Html::img($profileImage->getUrl('_org'), ['id' => 'crop-profile-image']) ?>

                <?= JCropWidget::widget([
                    'selector' => '#crop-profile-image',
                    'pluginOptions' => $model->getPluginOptions(),
                ]); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?= ModalButton::submitModal()?>
            <?= ModalButton::cancel()?>
        </div>

    <?php ActiveForm::end(); ?>

<?php ModalDialog::end() ?>

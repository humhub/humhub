<?php
/**
 * @var $this \humhub\components\View
 * @var \humhub\modules\admin\models\forms\FileSettingsForm $model
 * @var float $maxUploadSize
 * @var string $maxUploadSizeText
 * @var string $currentImageLibrary
 */

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;

?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = CActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <?= $form->labelEx($model, 'imageMagickPath'); ?>
    <?= $form->textField($model, 'imageMagickPath', ['class' => 'form-control', 'readonly' => Setting::IsFixed('imageMagickPath', 'file')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'Current Image Library: {currentImageLibrary}', ['{currentImageLibrary}' => $currentImageLibrary]); ?></p>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'maxFileSize'); ?>
    <?= $form->textField($model, 'maxFileSize', ['class' => 'form-control', 'readonly' => Setting::IsFixed('maxFileSize', 'file')]); ?>
    <p class="help-block" <?= ($model->maxFileSize > $maxUploadSize) ? 'style="color:'.$this->theme->variable('danger').' !important"' : ''?>>
        <?= Yii::t('AdminModule.views_setting_file', 'PHP reported a maximum of {maxUploadSize} MB', ['{maxUploadSize}' => $maxUploadSizeText]); ?>
    </p>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?= $form->checkBox($model, 'useXSendfile', ['disabled' => Setting::IsFixed('useXSendfile', 'file')]); ?>
            <?= $model->getAttributeLabel('useXSendfile'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'maxPreviewImageWidth'); ?>
    <?= $form->textField($model, 'maxPreviewImageWidth', ['class' => 'form-control', 'readonly' => Setting::IsFixed('maxPreviewImageWidth', 'file')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'If not set, width will default to 200px.') ?></p>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'maxPreviewImageHeight'); ?>
    <?= $form->textField($model, 'maxPreviewImageHeight', ['class' => 'form-control', 'readonly' => Setting::IsFixed('maxPreviewImageHeight', 'file')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'If not set, height will default to 200px.') ?></p>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?= $form->checkBox($model, 'hideImageFileInfo', ['disabled' => Setting::IsFixed('hideImageFileInfo', 'file')]); ?>
            <?= $model->getAttributeLabel('hideImageFileInfo'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'allowedExtensions'); ?>
    <?= $form->textField($model, 'allowedExtensions', ['class' => 'form-control']); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'Comma separated list. Leave empty to allow all.'); ?></p>
</div>

<hr>

<?= CHtml::submitButton(Yii::t('AdminModule.views_setting_file', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>

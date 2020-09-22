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

/** @var \humhub\modules\file\Module $fileModule */
$fileModule = Yii::$app->getModule('file');

?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = CActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <?= $form->labelEx($model, 'maxFileSize'); ?>
    <?= $form->textField($model, 'maxFileSize', ['class' => 'form-control', 'readonly' => $fileModule->settings->isFixed('maxFileSize')]); ?>
    <p class="help-block" <?= ($model->maxFileSize > $maxUploadSize) ? 'style="color:'.$this->theme->variable('danger').' !important"' : ''?>>
        <?= Yii::t('AdminModule.settings', 'PHP reported a maximum of {maxUploadSize} MB', ['{maxUploadSize}' => $maxUploadSizeText]); ?>
    </p>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?= $form->checkBox($model, 'useXSendfile', ['disabled' => $fileModule->settings->isFixed('useXSendfile')]); ?>
            <?= $model->getAttributeLabel('useXSendfile'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'maxPreviewImageWidth'); ?>
    <?= $form->textField($model, 'maxPreviewImageWidth', ['class' => 'form-control', 'readonly' => $fileModule->settings->isFixed('maxPreviewImageWidth')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.settings', 'If not set, width will default to 200px.') ?></p>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'maxPreviewImageHeight'); ?>
    <?= $form->textField($model, 'maxPreviewImageHeight', ['class' => 'form-control', 'readonly' => $fileModule->settings->isFixed('maxPreviewImageHeight')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.settings', 'If not set, height will default to 200px.') ?></p>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?= $form->checkBox($model, 'hideImageFileInfo', ['disabled' => $fileModule->settings->isFixed('hideImageFileInfo')]); ?>
            <?= $model->getAttributeLabel('hideImageFileInfo'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'allowedExtensions'); ?>
    <?= $form->textField($model, 'allowedExtensions', ['class' => 'form-control']); ?>
    <p class="help-block"><?= Yii::t('AdminModule.settings', 'Comma separated list. Leave empty to allow all.'); ?></p>
</div>

<hr>

<?= CHtml::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>

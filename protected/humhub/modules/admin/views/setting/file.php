<?php
/**
 * @var $this \humhub\modules\ui\view\components\View
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
    <div class="checkbox">
        <label>
            <?= $form->checkBox($model, 'excludeMediaFilesPreview', ['disabled' => $fileModule->settings->isFixed('excludeMediaFilesPreview')]); ?>
            <?= $model->getAttributeLabel('excludeMediaFilesPreview'); ?>
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

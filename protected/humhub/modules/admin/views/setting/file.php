<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php'); ?>

<?php $form = CActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <?= $form->labelEx($model, 'imageMagickPath'); ?>
    <?= $form->textField($model, 'imageMagickPath', array('class' => 'form-control', 'readonly' => Setting::IsFixed('imageMagickPath', 'file'))); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'Current Image Libary: {currentImageLibary}', array('{currentImageLibary}' => $currentImageLibary)); ?></p>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'maxFileSize'); ?>
    <?= $form->textField($model, 'maxFileSize', array('class' => 'form-control', 'readonly' => Setting::IsFixed('maxFileSize', 'file'))); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'PHP reported a maximum of {maxUploadSize} MB', array('{maxUploadSize}' => $maxUploadSize)); ?></p>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?= $form->checkBox($model, 'useXSendfile', array('disabled' => Setting::IsFixed('useXSendfile', 'file'))); ?>
            <?= $model->getAttributeLabel('useXSendfile'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'maxPreviewImageWidth'); ?>
    <?= $form->textField($model, 'maxPreviewImageWidth', array('class' => 'form-control', 'readonly' => Setting::IsFixed('maxPreviewImageWidth', 'file'))); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'If not set, width will default to 200px.') ?></p>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'maxPreviewImageHeight'); ?>
    <?= $form->textField($model, 'maxPreviewImageHeight', array('class' => 'form-control', 'readonly' => Setting::IsFixed('maxPreviewImageHeight', 'file'))); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'If not set, height will default to 200px.') ?></p>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?= $form->checkBox($model, 'hideImageFileInfo', array('disabled' => Setting::IsFixed('hideImageFileInfo', 'file'))); ?>
            <?= $model->getAttributeLabel('hideImageFileInfo'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'allowedExtensions'); ?>
    <?= $form->textField($model, 'allowedExtensions', array('class' => 'form-control')); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'Comma separated list. Leave empty to allow all.'); ?></p>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'showFilesWidgetBlacklist'); ?>
    <?= $form->textField($model, 'showFilesWidgetBlacklist', array('class' => 'form-control')); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_file', 'Comma separated list. Leave empty to show file list for all objects on wall.') ?></p>
</div>

<hr>

<?= CHtml::submitButton(Yii::t('AdminModule.views_setting_file', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>

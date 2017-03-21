<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php'); ?>

<?php $form = CActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?= $form->checkBox($model, 'enabled', array('readonly' => Setting::IsFixed('proxy.enabled'))); ?> <?= $model->getAttributeLabel('enabled'); ?>
        </label>
    </div>
</div>

<hr>

<div class="form-group">
    <?= $form->labelEx($model, 'server'); ?>
    <?= $form->textField($model, 'server', array('class' => 'form-control')); ?>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'port'); ?>
    <?= $form->textField($model, 'port', array('class' => 'form-control')); ?>
</div>

<?php if (defined('CURLOPT_PROXYUSERNAME')) { ?>
    <div class="form-group">
        <?= $form->labelEx($model, 'user'); ?>
        <?= $form->textField($model, 'user', array('class' => 'form-control')); ?>
    </div>
<?php } ?>

<?php if (defined('CURLOPT_PROXYPASSWORD')) { ?>
    <div class="form-group">
        <?= $form->labelEx($model, 'password'); ?>
        <?= $form->textField($model, 'password', array('class' => 'form-control')); ?>
    </div>
<?php } ?>

<?php if (defined('CURLOPT_NOPROXY')) { ?>
    <div class="form-group">
        <?= $form->labelEx($model, 'noproxy'); ?>
        <?= $form->textArea($model, 'noproxy', array('class' => 'form-control', 'rows' => '4')); ?>
    </div>
<?php } ?>

<hr>

<?= CHtml::submitButton(Yii::t('AdminModule.views_setting_proxy', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>

<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>

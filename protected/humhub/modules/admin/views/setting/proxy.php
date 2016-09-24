<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>


<?php $form = CActiveForm::begin(); ?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?php echo $form->checkBox($model, 'enabled', array('readonly' => Setting::IsFixed('proxy.enabled'))); ?> <?php echo $model->getAttributeLabel('enabled'); ?>
        </label>
    </div>
</div>

<hr>
<div class="form-group">
    <?php echo $form->labelEx($model, 'server'); ?>
    <?php echo $form->textField($model, 'server', array('class' => 'form-control')); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'port'); ?>
    <?php echo $form->textField($model, 'port', array('class' => 'form-control')); ?>
</div>

<?php if (defined('CURLOPT_PROXYUSERNAME')) { ?>
    <div class="form-group">
        <?php echo $form->labelEx($model, 'user'); ?>
        <?php echo $form->textField($model, 'user', array('class' => 'form-control')); ?>
    </div>
<?php } ?>

<?php if (defined('CURLOPT_PROXYPASSWORD')) { ?>
    <div class="form-group">
        <?php echo $form->labelEx($model, 'password'); ?>
        <?php echo $form->textField($model, 'password', array('class' => 'form-control')); ?>
    </div>
<?php } ?>

<?php if (defined('CURLOPT_NOPROXY')) { ?>
    <div class="form-group">
        <?php echo $form->labelEx($model, 'noproxy'); ?>
        <?php echo $form->textArea($model, 'noproxy', array('class' => 'form-control', 'rows' => '4')); ?>
    </div>
<?php } ?>

<hr>
<?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_proxy', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?php echo \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>

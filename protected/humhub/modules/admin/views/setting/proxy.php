<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
use yii\helpers\Url;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_proxy', '<strong>Proxy</strong> settings'); ?></div>
    <div class="panel-body">

        <?php $form = CActiveForm::begin(); ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'enabled', array('readonly' => Setting::IsFixed('enabled', 'proxy'))); ?> <?php echo $model->getAttributeLabel('enabled'); ?>
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
        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_proxy', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>
        <?php CActiveForm::end(); ?>

    </div>
</div>





<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
use yii\helpers\Url;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_statistic', '<strong>Statistic</strong> settings'); ?></div>
    <div class="panel-body">

        <?php $form = CActiveForm::begin(); ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'trackingHtmlCode'); ?>
            <?php echo $form->textArea($model, 'trackingHtmlCode', array('class' => 'form-control', 'rows' => '8')); ?>
        </div>
        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_statistic', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>
        <?php CActiveForm::end(); ?>

    </div>
</div>
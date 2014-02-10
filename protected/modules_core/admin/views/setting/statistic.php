<h1><?php echo Yii::t('AdminModule.base', 'Statistic - Settings'); ?></h1><br>
<?php $form = $this->beginWidget('CActiveForm', array(
    'id' => 'statistic-settings-form',
    'enableAjaxValidation' => false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'trackingHtmlCode'); ?>
    <?php echo $form->textArea($model, 'trackingHtmlCode', array('class' => 'form-control', 'rows' => '8')); ?>
</div>
<hr>

<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<?php $this->endWidget(); ?>












<h1><?php echo Yii::t('YiiGiiModule.base', 'Gii Configuration'); ?></h1>

<p><?php echo Yii::t('YiiGiiModule.base', 'You can access Gii via: {url}.', array('{url}' => CHtml::link($this->createAbsoluteUrl('//gii'),$this->createUrl('//gii')))); ?>

<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'configure-form',
    'enableAjaxValidation' => true,
        ));
?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'password'); ?>
    <?php echo $form->textField($model, 'password', array('class' => 'form-control')); ?>
    <?php echo $form->error($model, 'password'); ?>
</div>


<div class="form-group">
    <?php echo $form->labelEx($model, 'ipFilters'); ?>
    <?php echo $form->textField($model, 'ipFilters', array('class' => 'form-control')); ?>
    <?php echo $form->error($model, 'ipFilters'); ?>
    <p class="help-block"><?php echo Yii::t('YiiGiiModule.base', 'If removed, Gii defaults to localhost only. Edit carefully to taste.'); ?></p>
</div>


<hr>
<?php echo CHtml::submitButton(Yii::t('base', 'Save'), array('class' => 'btn btn-primary')); ?>
<a class="btn btn-default" href="<?php echo $this->createUrl('//admin/module'); ?>"><?php echo Yii::t('AdminModule.base', 'Back to modules'); ?></a>

<?php $this->endWidget(); ?>

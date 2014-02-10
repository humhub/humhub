<h1><?php echo Yii::t('AdminModule.base', 'Security Settings and Roles'); ?></h1><br>
<?php $form = $this->beginWidget('CActiveForm', array(
    'id' => 'security-settings-form',
    'enableAjaxValidation' => false,
)); ?>

<?php echo $form->errorSummary($model); ?>


<?php echo $form->checkBox($model, 'canAdminAlwaysDeleteContent'); ?>

<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<?php $this->endWidget(); ?>











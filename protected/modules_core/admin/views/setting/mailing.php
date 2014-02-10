<h1><?php echo Yii::t('AdminModule.base', 'Mailing - Settings'); ?></h1><br>
<?php $form = $this->beginWidget('CActiveForm', array(
    'id' => 'mailing-settings-form',
    'enableAjaxValidation' => false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'systemEmailAddress'); ?>
    <?php echo $form->textField($model, 'systemEmailAddress', array('class' => 'form-control')); ?>
</div>


<div class="form-group">
    <?php echo $form->labelEx($model, 'systemEmailName'); ?>
    <?php echo $form->textField($model, 'systemEmailName', array('class' => 'form-control')); ?>
</div>


<div class="form-group">
    <?php echo $form->labelEx($model, 'transportType'); ?>
    <?php echo $form->dropDownList($model, 'transportType', $transportTypes, array('class' => 'form-control')); ?>
</div>

<hr>
<h4> <?php echo Yii::t('AdminModule.base', 'SMTP Options'); ?> </h4>

<div class="form-group">
    <?php echo $form->labelEx($model, 'hostname'); ?>
    <?php echo $form->textField($model, 'hostname', array('class' => 'form-control')); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'username'); ?>
    <?php echo $form->textField($model, 'username', array('class' => 'form-control')); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'password'); ?>
    <?php echo $form->passwordField($model, 'password', array('class' => 'form-control')); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'port'); ?>
    <?php echo $form->textField($model, 'port', array('class' => 'form-control')); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'encryption'); ?>
    <?php echo $form->dropDownList($model, 'encryption', $encryptionTypes, array('class' => 'form-control')); ?>
</div>

<hr>
<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<?php $this->endWidget(); ?>






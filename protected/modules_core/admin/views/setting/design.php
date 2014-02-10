<h1><?php echo Yii::t('AdminModule.base', 'Design - Settings'); ?></h1><br>
<?php $form = $this->beginWidget('CActiveForm', array(
    'id' => 'design-settings-form',
    'enableAjaxValidation' => false,
)); ?>

<?php echo $form->errorSummary($model); ?><br>

<div class="form-group">
    <?php echo $form->labelEx($model, 'theme'); ?>
    <?php echo $form->dropDownList($model, 'theme', $themes, array('class' => 'form-control')); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'paginationSize'); ?>
    <?php echo $form->textField($model, 'paginationSize', array('class' => 'form-control')); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'displayName'); ?>
    <?php echo $form->dropDownList($model, 'displayName', array('{username}'=>Yii::t('AdminModule.design','Username (e.g. john)'), '{profile.firstname} {profile.lastname}' => Yii::t('AdminModule.design','Firstname Lastname (e.g. John Doe)')), array('class' => 'form-control')); ?>
</div>

<hr>
<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<?php $this->endWidget(); ?>







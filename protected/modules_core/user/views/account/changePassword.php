<div class="panel-heading">
    <?php echo Yii::t('UserModule.base', 'Change password'); ?>
</div>
<div class="panel-body">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'user-form',
        'enableAjaxValidation' => false,
    ));
    ?>


    <?php //echo $form->errorSummary($model); ?>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'currentPassword'); ?>
        <?php echo $form->passwordField($model, 'currentPassword', array('class' => 'form-control', 'maxlength' => 45)); ?>
        <?php echo $form->error($model, 'currentPassword'); ?>
    </div>
    <hr/>
    <div class="form-group">
        <?php echo $form->labelEx($model, 'newPassword'); ?>
        <?php echo $form->passwordField($model, 'newPassword', array('class' => 'form-control', 'maxlength' => 45)); ?>
        <?php echo $form->error($model, 'newPassword'); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'newPasswordVerify'); ?>
        <?php echo $form->passwordField($model, 'newPasswordVerify', array('class' => 'form-control', 'maxlength' => 45)); ?>
        <?php echo $form->error($model, 'newPasswordVerify'); ?>
    </div>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>


    <?php $this->endWidget(); ?>

</div>

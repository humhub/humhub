<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_changePassword', '<strong>Change</strong> password'); ?>
</div>
<div class="panel-body">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'user-form',
        'enableAjaxValidation' => false,
    ));
    ?>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'currentPassword'); ?>
        <?php echo $form->passwordField($model, 'currentPassword', array('class' => 'form-control', 'maxlength' => 255, 'value' => '')); ?>
        <?php echo $form->error($model, 'currentPassword'); ?>
    </div>
    <hr/>
    <div class="form-group">
        <?php echo $form->labelEx($model, 'newPassword'); ?>
        <?php echo $form->passwordField($model, 'newPassword', array('class' => 'form-control', 'maxlength' => 255, 'value' => '')); ?>
        <?php echo $form->error($model, 'newPassword'); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'newPasswordConfirm'); ?>
        <?php echo $form->passwordField($model, 'newPasswordConfirm', array('class' => 'form-control', 'maxlength' => 255, 'value' => '')); ?>
        <?php echo $form->error($model, 'newPasswordConfirm'); ?>
    </div>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_changePassword', 'Save'), array('class' => 'btn btn-primary')); ?>


    <?php $this->endWidget(); ?>

</div>

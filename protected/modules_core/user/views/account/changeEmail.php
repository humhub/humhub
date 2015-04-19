<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_changeEmail', '<strong>Change</strong> E-mail'); ?>
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
    	<?php echo Yii::t('UserModule.views_account_changeEmail', '<strong>Current E-mail address</strong>'); ?>
    	<br /><?php echo CHtml::encode(Yii::app()->user->getModel()->email) ?>
    </div>
    <hr/>
    <div class="form-group">
        <?php echo $form->labelEx($model, 'currentPassword'); ?>
        <?php echo $form->passwordField($model, 'currentPassword', array('class' => 'form-control', 'maxlength' => 45)); ?>
        <?php echo $form->error($model, 'currentPassword'); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'newEmail'); ?>
        <?php echo $form->textField($model, 'newEmail', array('class' => 'form-control', 'maxlength' => 45)); ?>
        <?php echo $form->error($model, 'newEmail'); ?>
    </div>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_changeEmail', 'Save'), array('class' => 'btn btn-primary')); ?>

    <?php $this->endWidget(); ?>
</div>





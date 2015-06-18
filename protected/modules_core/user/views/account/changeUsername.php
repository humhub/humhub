<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_changeUsername', '<strong>Change</strong> username'); ?>
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
    	<?php echo Yii::t('UserModule.views_account_changeUsername', '<strong>Current username</strong>'); ?>
    	<br /><?php echo CHtml::encode(Yii::app()->user->getModel()->username) ?>
    </div>
    <hr/>
    <div class="form-group">
        <?php echo $form->labelEx($model, 'currentPassword'); ?>
        <?php echo $form->passwordField($model, 'currentPassword', array('class' => 'form-control', 'maxlength' => 45)); ?>
        <?php echo $form->error($model, 'currentPassword'); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'newUsername'); ?>
        <?php echo $form->textField($model, 'newUsername', array('class' => 'form-control', 'maxlength' => 25)); ?>
        <?php echo $form->error($model, 'newUsername'); ?>
    </div>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_changeUsername', 'Save'), array('class' => 'btn btn-primary')); ?>

    <?php $this->endWidget(); ?>
</div>





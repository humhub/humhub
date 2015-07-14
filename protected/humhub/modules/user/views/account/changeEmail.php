<?php

use \humhub\compat\CActiveForm;
use \humhub\compat\CHtml;
?>

<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_changeEmail', '<strong>Change</strong> E-mail'); ?>
</div>
<div class="panel-body">
    <?php $form = CActiveForm::begin(); ?>

    <div class="form-group">
        <?php echo Yii::t('UserModule.views_account_changeEmail', '<strong>Current E-mail address</strong>'); ?>
        <br /><?php echo CHtml::encode(Yii::$app->user->getIdentity()->email) ?>
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

    <!-- show flash message after saving -->
    <?php echo \humhub\widgets\DataSaved::widget(); ?>

    <?php CActiveForm::end(); ?>
</div>





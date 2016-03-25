<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
?>
<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_changePassword', '<strong>Change</strong> password'); ?>
</div>
<div class="panel-body">
    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
    
    <hr>

    <?php echo $form->field($model, 'newPassword')->passwordInput(['maxlength' => 45]); ?>

    <?php echo $form->field($model, 'newPasswordConfirm')->passwordInput(['maxlength' => 45]); ?>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_changePassword', 'Save'), array('class' => 'btn btn-primary')); ?>


    <!-- show flash message after saving -->
    <?php echo \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>

</div>

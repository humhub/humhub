<?php

use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;
?>

<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_changeEmail', '<strong>Change</strong> E-mail'); ?>
</div>
<div class="panel-body">
    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group">
        <?php echo Yii::t('UserModule.views_account_changeEmail', '<strong>Current E-mail address</strong>'); ?>
        <br /><?php echo CHtml::encode(Yii::$app->user->getIdentity()->email) ?>
    </div>
    <hr>

    <?php echo $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>


    <?php echo $form->field($model, 'newEmail')->textInput(['maxlength' => 45]); ?>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_changeEmail', 'Save'), array('class' => 'btn btn-primary')); ?>

    <!-- show flash message after saving -->
    <?php echo \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
</div>





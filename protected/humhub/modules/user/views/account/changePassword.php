<?php

use humhub\widgets\ActiveForm;
use yii\bootstrap\Html;
?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php'); ?>
    <div class="help-block">
         <?= Yii::t('UserModule.views_account_changePassword', 'Your current password can be changed here.') ?>
    </div>
    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->isAttributeSafe('currentPassword')): ?>
        <?= $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
        <hr>
    <?php endif; ?>

    <?= $form->field($model, 'newPassword')->passwordInput(['maxlength' => 45]); ?>

    <?= $form->field($model, 'newPasswordConfirm')->passwordInput(['maxlength' => 45]); ?>

    <hr>
    <?= Html::submitButton(Yii::t('UserModule.views_account_changePassword', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

    <?= \humhub\widgets\DataSaved::widget(); ?>
    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>

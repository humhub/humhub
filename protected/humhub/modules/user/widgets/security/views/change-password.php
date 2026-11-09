<?php

use humhub\modules\user\models\Password;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var Password $model */
?>
<?php $form = ActiveForm::begin(['action' => ['/user/security/change-password'], 'acknowledge' => true, 'sudo' => true]) ?>

<?= $form->field($model, 'newPassword')->passwordInput(['maxlength' => 45, 'autocomplete' => 'new-password']) ?>

<?= $form->field($model, 'newPasswordConfirm')->passwordInput(['maxlength' => 45, 'autocomplete' => 'new-password']) ?>

<p class="text-body-secondary">
    <?= Yii::t('UserModule.account', 'Changing your password logs you out on all other devices.') ?>
</p>

<?= Button::save()->submit() ?>

<?php ActiveForm::end() ?>

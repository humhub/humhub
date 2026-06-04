<?php

use humhub\modules\user\models\Password;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var Password $model */
?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
<div class="text-body-secondary">
    <?= Yii::t('UserModule.account', 'Your current password can be changed here.') ?>
</div>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?php if ($model->isAttributeSafe('currentPassword')): ?>
    <?= $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45, 'autocomplete' => 'current-password']) ?>
    <hr>
<?php endif ?>

<?= $form->field($model, 'newPassword')->passwordInput(['maxlength' => 45, 'autocomplete' => 'new-password']) ?>

<?= $form->field($model, 'newPasswordConfirm')->passwordInput(['maxlength' => 45, 'autocomplete' => 'new-password']); ?>

<?= Button::save()->submit() ?>

<?php ActiveForm::end() ?>
<?php $this->endContent() ?>

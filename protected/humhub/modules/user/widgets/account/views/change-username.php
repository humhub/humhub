<?php

use humhub\modules\user\models\forms\AccountChangeUsername;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var AccountChangeUsername $model */
?>
<?php $form = ActiveForm::begin(['action' => ['/user/account/change-username'], 'acknowledge' => true]) ?>

<p class="text-body-secondary">
    <?= Yii::t('UserModule.account', 'Changing the username can make some links unusable, for example old links to the profile.') ?>
</p>

<?php if ($model->isAttributeRequired('currentPassword')): ?>
    <?= $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45, 'autocomplete' => 'current-password']) ?>
<?php endif ?>

<?= $form->field($model, 'newUsername')->textInput(['maxlength' => 45, 'autocomplete' => 'off']) ?>

<?= Button::save()->submit() ?>

<?php ActiveForm::end() ?>

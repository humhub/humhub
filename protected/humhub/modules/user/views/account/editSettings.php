<?php

use humhub\modules\user\models\forms\AccountSettings;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var AccountSettings $model */

$editable = $model->canChangeUsername() || $model->canChangeEmail() || $model->isTourAvailable();
?>
<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>
<?php $form = ActiveForm::begin(['id' => 'account-settings-form', 'acknowledge' => true, 'sudo' => true]) ?>

<?= $form->field($model, 'username')->textInput([
    'maxlength' => 45,
    'autocomplete' => 'username',
    'disabled' => !$model->canChangeUsername(),
    'data-sudo-field' => true,
])->hint($model->canChangeUsername()
    ? Yii::t('UserModule.account', 'Changing the username can make some links unusable, for example old links to the profile.')
    : false) ?>

<?= $form->field($model, 'email')->input('email', [
    'maxlength' => 150,
    'autocomplete' => 'email',
    'disabled' => !$model->canChangeEmail(),
    'data-sudo-field' => true,
])->hint($model->canChangeEmail()
    ? Yii::t('UserModule.account', 'A new address is used once you have confirmed it by the link we send to it.')
    : false) ?>

<?php if ($model->isTourAvailable()): ?>
    <?= $form->field($model, 'show_introduction_tour')->checkbox() ?>
<?php endif ?>

<?php if ($editable): ?>
    <?= Button::save()->submit() ?>
<?php endif ?>

<?php ActiveForm::end() ?>
<?php $this->endContent() ?>

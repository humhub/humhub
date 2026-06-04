<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\AccountChangeEmail;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var AccountChangeEmail $model */
?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
<div class="text-body-secondary">
    <?= Yii::t('UserModule.account', 'Your current E-mail address is <b>{email}</b>. You can change your current E-mail address here.', [
        'email' => Html::encode(Yii::$app->user->getIdentity()->email),
    ]) ?>
</div>
<?php $form = ActiveForm::begin(['acknowledge' => true]) ?>

<?php if ($model->isAttributeRequired('currentPassword')): ?>
    <?= $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45, 'autocomplete' => 'current-password']) ?>
<?php endif ?>

<?= $form->field($model, 'newEmail')->input('email', ['maxlength' => 150, 'autocomplete' => 'email']) ?>

<?= Button::save()->submit() ?>

<?php ActiveForm::end() ?>
<?php $this->endContent() ?>

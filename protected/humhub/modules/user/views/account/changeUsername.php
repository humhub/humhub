<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\AccountChangeUsername;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var AccountChangeUsername $model */
?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
<div class="text-body-secondary">
    <?= Yii::t('UserModule.account', 'Your current username is <b>{username}</b>. You can change your current username here.', [
        'username' => Html::encode(Yii::$app->user->getIdentity()->username),
    ]) ?>
</div>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?php if ($model->isAttributeRequired('currentPassword')): ?>
    <?= $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45, 'autocomplete' => 'current-password']) ?>
<?php endif ?>

<?= $form->field($model, 'newUsername')->textInput(['maxlength' => 45]) ?>

<?= Button::save()->submit() ?>

<?php ActiveForm::end() ?>
<?php $this->endContent() ?>

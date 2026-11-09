<?php

use humhub\modules\user\models\forms\AccountChangeEmail;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var AccountChangeEmail $model */
?>
<?php $form = ActiveForm::begin(['action' => ['/user/account/change-email'], 'acknowledge' => true, 'sudo' => true]) ?>

<p class="text-body-secondary">
    <?= Yii::t('UserModule.account', 'A new address is used once you have confirmed it by the link we send to it.') ?>
</p>

<?= $form->field($model, 'newEmail')->input('email', ['maxlength' => 150, 'autocomplete' => 'email']) ?>

<?= Button::save()->submit() ?>

<?php ActiveForm::end() ?>

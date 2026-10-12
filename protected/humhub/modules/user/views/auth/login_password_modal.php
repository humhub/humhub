<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\LoginPassword;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $model LoginPassword */
/* @var $passwordRecoveryRoute string|array|null */
/* @var $signUpAllowed bool */

?>

<?php Modal::beginDialog([
    'id' => 'user-auth-login-modal',
    'title' => Yii::t('UserModule.auth', 'Sign In'),
]) ?>

    <?php $form = ActiveForm::begin(['id' => 'account-login-form-modal', 'enableClientValidation' => false]) ?>
        <div class="d-flex align-items-baseline gap-1 mb-3 text-nowrap">
            <?= Yii::t('UserModule.auth', 'Sign In as {username}', [
                // Real (read-only) username field, so browser password managers pair it with the password
                'username' => Html::textInput('Login[username]', $model->username, [
                    'id' => 'login_username',
                    'autocomplete' => 'username',
                    'readonly' => true,
                    'tabindex' => -1,
                    'title' => $model->username,
                    'aria-label' => $model->getAttributeLabel('username'),
                    'class' => 'form-control-plaintext fw-bold p-0 flex-grow-1',
                    'style' => 'min-width: 0',
                ]),
            ]) ?>
        </div>
        <p class="mb-2"><?= $model->getAttributeLabel('password') ?></p>
        <?= $form->field($model, 'password')->passwordInput([
            'id' => 'login_password',
            'placeholder' => $model->getAttributeLabel('password'),
            'autocomplete' => 'current-password',
            'autofocus' => true,
        ])->label(false) ?>
        <?= $model->hideRememberMe ? '' : $form->field($model, 'rememberMe')->checkbox() ?>

        <div class="row g-3">
            <div class="col-6">
                <?= ModalButton::light(Yii::t('UserModule.auth', 'Back'))
                    ->load(['/user/auth/login', 'forget' => 1])
                    ->cssClass('w-100') ?>
            </div>
            <div class="col-6">
                <?= ModalButton::save(Yii::t('UserModule.auth', 'Sign In'))
                    ->submit(['/user/auth/password'])
                    ->id('login-button')
                    ->cssClass('w-100') ?>
            </div>
        </div>

        <?php if ($passwordRecoveryRoute): ?>
            <div class="text-center mt-3">
                <?= is_array($passwordRecoveryRoute)
                    ? Link::modal(Yii::t('UserModule.auth', 'Forgot password?'))
                        ->id('recoverPasswordBtn')
                        ->load($passwordRecoveryRoute)
                    : Link::to(Yii::t('UserModule.auth', 'Forgot password?'), $passwordRecoveryRoute)
                        ->id('recoverPasswordBtn')
                        ->blank() ?>
            </div>
        <?php endif; ?>

        <?php if ($signUpAllowed && $model->hasErrors()): ?>
            <small class="text-center mt-2 d-block">
                <?= Yii::t('UserModule.auth', "Don't have an account?") ?>
                <?= Link::modal(Yii::t('UserModule.auth', 'Sign Up'))
                    ->id('register-link-modal')
                    ->load(['/user/auth/register']) ?>
            </small>
        <?php endif; ?>

    <?php ActiveForm::end() ?>

<?php Modal::endDialog() ?>

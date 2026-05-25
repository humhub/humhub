<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\LoginPassword;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $model LoginPassword */
/* @var $passwordRecoveryRoute string|array|null */
/* @var $signUpAllowed bool */

?>

<?php Modal::beginDialog([
    'id' => 'user-auth-login-modal',
    'title' => Yii::t('UserModule.auth', 'Sign In'),
]) ?>

    <?php $form = ActiveForm::begin(['id' => 'account-login-form-modal', 'enableClientValidation' => false]) ?>
        <p class="mb-2"><?= $model->getAttributeLabel('password') ?></p>
        <?= $form->field($model, 'password')->passwordInput([
            'id' => 'login_password',
            'placeholder' => $model->getAttributeLabel('password'),
            'autocomplete' => 'current-password',
        ])->label(false) ?>
        <?= $model->hideRememberMe ? '' : $form->field($model, 'rememberMe')->checkbox() ?>
        <?= $form->field($model, 'rememberUsername')->checkbox() ?>

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

<script <?= Html::nonce() ?>>
    $(document).on('humhub:ready', function () {
        $('#login_password').focus();

        // "Remember username" is redundant when "Keep me signed in" is on
        // (the long-lived session cookie covers both), so hide+uncheck it then.
        // Yii ActiveForm renders a sibling hidden input with the same name to
        // submit "0" for unchecked — must filter to the checkbox specifically.
        var $modal = $('#user-auth-login-modal');
        var $rm = $modal.find('input[type="checkbox"][name="Login[rememberMe]"]');
        var $ru = $modal.find('input[type="checkbox"][name="Login[rememberUsername]"]');
        var $ruWrap = $modal.find('.field-login-rememberusername');
        var sync = function () {
            if ($rm.prop('checked')) {
                $ru.prop('checked', false);
                $ruWrap.hide();
            } else {
                $ruWrap.show();
            }
        };
        $rm.on('change', sync);
        sync();
    });
</script>

<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\LoginPassword;
use humhub\widgets\bootstrap\Button;
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
        <?= $form->field($model, 'password')->passwordInput([
            'id' => 'login_password',
            'placeholder' => $model->getAttributeLabel('password'),
            'autocomplete' => 'current-password',
        ]) ?>
        <?= $model->hideRememberMe ? '' : $form->field($model, 'rememberMe')->checkbox() ?>
        <?= $form->field($model, 'rememberUsername')->checkbox() ?>

        <div class="modal-body-footer">
            <div class="d-flex flex-column align-center-end w-100">
                <div class="row g-3">
                    <div class="col-6">
                        <?= Button::light(Yii::t('UserModule.auth', 'Back'))
                            ->link(['/user/auth/login', 'forget' => 1])
                            ->cssClass('w-100')
                            ->pjax(false) ?>
                    </div>
                    <div class="col-6">
                        <?= ModalButton::save(Yii::t('UserModule.auth', 'Sign In'))
                            ->submit(['/user/auth/password'])
                            ->id('login-button')
                            ->cssClass('w-100') ?>
                    </div>
                </div>

                <?php if ($passwordRecoveryRoute): ?>
                    <?= Html::a(
                        Html::tag('br') . Yii::t('UserModule.auth', 'Forgot your password?'),
                        $passwordRecoveryRoute,
                        ArrayHelper::merge([
                            'id' => 'recoverPasswordBtn',
                        ], is_array($passwordRecoveryRoute) ? [
                            'data' => [
                                'action-click' => 'ui.modal.load',
                                'action-url' => Url::to($passwordRecoveryRoute),
                            ],
                        ] : [
                            'target' => '_blank',
                        ]),
                    ) ?>
                <?php endif; ?>

                <?php if ($signUpAllowed && $model->hasErrors()): ?>
                    <small>
                        <br>
                        <?= Yii::t('UserModule.auth', "Don't have an account?") ?>
                        <?= Html::a(
                            Yii::t('UserModule.auth', 'Sign Up'),
                            ['/user/auth/register'],
                            [
                                'id' => 'register-link-modal',
                                'data' => [
                                    'action-click' => 'ui.modal.load',
                                    'action-url' => Url::to(['/user/auth/register']),
                                ],
                            ],
                        ) ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>

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

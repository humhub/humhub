<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\LoginPassword;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Login');

/* @var $this \humhub\components\View */
/* @var $model LoginPassword */
/* @var $passwordRecoveryRoute string|array|null */
/* @var $signUpAllowed bool */

?>

<div id="user-auth-login" class="container container-login">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default animated bounceIn" id="login-form">

        <div class="panel-heading"><?= Yii::t('UserModule.auth', 'Please sign in') ?></div>

        <div class="panel-body">

            <div id="login-identity" class="d-flex align-items-center justify-content-between mb-3 p-2 border rounded">
                <div class="d-flex align-items-center">
                    <a href="<?= Url::to(['/user/auth/login', 'forget' => 1]) ?>"
                       id="login-back"
                       class="me-2 link-accent"
                       data-pjax-prevent
                       aria-label="<?= Html::encode(Yii::t('UserModule.auth', 'Use a different account')) ?>"
                       title="<?= Html::encode(Yii::t('UserModule.auth', 'Use a different account')) ?>">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <span><?= Html::encode($model->username) ?></span>
                </div>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'account-login-form', 'enableClientValidation' => false]) ?>
                <?= $form->field($model, 'password')->passwordInput([
                    'id' => 'login_password',
                    'placeholder' => $model->getAttributeLabel('password'),
                    'aria-label' => $model->getAttributeLabel('password'),
                    'autocomplete' => 'current-password',
                    'autofocus' => true,
                ])->label(false) ?>

                <?= $model->hideRememberMe ? '' : $form->field($model, 'rememberMe')->checkbox() ?>
                <?= $form->field($model, 'rememberUsername')->checkbox() ?>

                <?= Html::submitButton(
                    Yii::t('UserModule.auth', 'Sign in'),
                    ['id' => 'login-button', 'data-ui-loader' => '', 'class' => 'btn btn-large btn-primary w-100'],
                ) ?>

                <?php if ($passwordRecoveryRoute) : ?>
                    <div class="text-center mt-2">
                        <small>
                            <?= Html::a(
                                Yii::t('UserModule.auth', 'Forgot your password?'),
                                $passwordRecoveryRoute,
                                [
                                    'id' => 'password-recovery-link',
                                    'class' => 'link-accent',
                                    'target' => is_array($passwordRecoveryRoute) ? '_self' : '_blank',
                                    'data' => [
                                        'pjax-prevent' => true,
                                    ],
                                ],
                            ) ?>
                        </small>
                    </div>
                <?php endif; ?>

                <?php if ($signUpAllowed && $model->hasErrors()): ?>
                    <div class="text-center mt-2">
                        <small>
                            <?= Yii::t('UserModule.auth', "Don't have an account?") ?>
                            <?= Html::a(
                                Yii::t('UserModule.auth', 'Sign up'),
                                Url::to(['/user/auth/register']),
                                [
                                    'id' => 'register-link',
                                    'class' => 'link-accent',
                                    'data' => ['pjax-prevent' => true],
                                ],
                            ) ?>
                        </small>
                    </div>
                <?php endif; ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <br>

    <?= LanguageChooser::widget(['vertical' => true, 'hideLabel' => true]) ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        $('#login_password').focus();

        // "Remember username" is redundant when "Keep me signed in" is on
        // (the long-lived session cookie covers both), so hide+uncheck it then.
        // Yii ActiveForm renders a sibling hidden input with the same name to
        // submit "0" for unchecked — must filter to the checkbox specifically.
        var $rm = $('input[type="checkbox"][name="Login[rememberMe]"]');
        var $ru = $('input[type="checkbox"][name="Login[rememberUsername]"]');
        var $ruWrap = $('.field-login-rememberusername');
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

    <?php if ($model->hasErrors()) { ?>
    $('#login-form').removeClass('bounceIn').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php } ?>
</script>

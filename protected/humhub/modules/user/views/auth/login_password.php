<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\user\models\forms\LoginPassword;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;

$this->pageTitle = Yii::t('UserModule.auth', 'Sign In');

/* @var $this View */
/* @var $model LoginPassword */
/* @var $passwordRecoveryRoute string|array|null */
/* @var $signUpAllowed bool */

?>

<div id="user-auth-login" class="container container-login">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default mb-4 animated bounceIn" id="login-form">
        <div class="panel-heading">
            <strong class="fw-bolder"><?= Yii::t('UserModule.auth', 'Sign In') ?></strong>
        </div>
        <div class="panel-body pt-0">
            <?php $form = ActiveForm::begin(['id' => 'account-login-form', 'enableClientValidation' => false]) ?>
                <p class="mb-2"><?= $model->getAttributeLabel('password') ?></p>
                <?= $form->field($model, 'password')->passwordInput([
                    'id' => 'login_password',
                    'placeholder' => $model->getAttributeLabel('password'),
                    'aria-label' => $model->getAttributeLabel('password'),
                    'autocomplete' => 'current-password',
                    'autofocus' => true,
                ])->label(false) ?>

                <?= $model->hideRememberMe ? '' : $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="row g-3">
                    <div class="col-6">
                        <?= Button::light(Yii::t('UserModule.auth', 'Back'))
                            ->link(['/user/auth/login', 'forget' => 1])
                            ->cssClass('w-100')
                            ->pjax(false) ?>
                    </div>
                    <div class="col-6">
                        <?= Button::save(Yii::t('UserModule.auth', 'Sign In'))
                            ->id('login-button')
                            ->submit()
                            ->cssClass('w-100') ?>
                    </div>
                </div>

                <?php if ($passwordRecoveryRoute) : ?>
                    <div class="text-center mt-3">
                        <?= Link::to(Yii::t('UserModule.auth', 'Forgot password?'), $passwordRecoveryRoute)
                            ->id('password-recovery-link')
                            ->target(is_array($passwordRecoveryRoute) ? '_self' : '_blank')
                            ->cssClass('link-accent')
                            ->pjax(false) ?>
                    </div>
                <?php endif; ?>

                <?php if ($signUpAllowed && $model->hasErrors()): ?>
                    <div class="text-center mt-2">
                        <small>
                            <?= Yii::t('UserModule.auth', "Don't have an account?") ?>
                            <?= Link::to(Yii::t('UserModule.auth', 'Sign Up'), ['/user/auth/register'])
                                ->id('register-link')
                                ->cssClass('link-accent')
                                ->pjax(false) ?>
                        </small>
                    </div>
                <?php endif; ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= LanguageChooser::widget(['vertical' => true]) ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        $('#login_password').focus();
    });

    <?php if ($model->hasErrors()) { ?>
    $('#login-form').removeClass('bounceIn').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php } ?>
</script>

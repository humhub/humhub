<?php

use humhub\libs\Html;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\Invite;
use yii\captcha\Captcha;
use \yii\helpers\Url;
use yii\widgets\ActiveForm;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\SiteLogo;

$this->pageTitle = Yii::t('UserModule.auth', 'Login');

/* @var $canRegister boolean */
/* @var $model Login */
/* @var $invite Invite */
/* @var $info string */
?>

<div class="container container-login">
    <?= SiteLogo::widget(['place' => 'login']); ?>

    <?php if (!isset($_GET['isSignup'])) : ?>
        <div class="panel panel-default panel-login" id="login-form">

            <div class="user-icon">
                <i class="fa fa-user-o" aria-hidden="true"></i>
            </div>

            <div class="panel-body panel-body-login">

                <?php if (Yii::$app->session->hasFlash('error')) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= Yii::$app->session->getFlash('error') ?>
                    </div>
                <?php endif; ?>

                <p class='text-line'><?= Yii::t('UserModule.auth', "Welcome back!"); ?></p>

                <?php $form = ActiveForm::begin(['id' => 'account-login-form', 'enableClientValidation' => false]); ?>
                <?= $form->field($model, 'username')
                    ->textInput(['id' => 'login_username', 'placeholder' => $model->getAttributeLabel('Username'), 'aria-label' => $model->getAttributeLabel('username'), 'class' => 'form-control input-login', 'maxlength' => '50'])
                    ->label(false); ?>
                <?= $form->field($model, 'password')
                    ->passwordInput(['id' => 'login_password', 'placeholder' => $model->getAttributeLabel('Password'), 'aria-label' => $model->getAttributeLabel('password'), 'class' => 'form-control input-login', 'maxlength' => '25'])
                    ->label(false); ?>

                <div class="row btn-login-container">
                    <?= Html::submitButton(Yii::t('UserModule.auth', 'Login'), ['id' => 'login-button', 'data-ui-loader' => "", 'class' => 'btn btn-large btn-login']); ?>
                </div>
                <div class="row question-container">
                    <p><?= Yii::t('UserModule.auth', "Don't have an account yet?"); ?></p>
                    <a id="password-recovery-link" href="<?= Url::toRoute('/user/auth/login?isSignup=true'); ?>" data-pjax-prevent><?= Yii::t('UserModule.auth', "Sign up") ?></a>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['isSignup'])) : ?>
        <?php if ($canRegister) : ?>
            <div id="register-form" class="panel panel-default panel-login">

                <div class="user-icon">
                    <i class="fa fa-user-o" aria-hidden="true"></i>
                </div>

                <div class="panel-body panel-body-login">

                    <p class='text-line'><?= Yii::t('UserModule.auth', "Sign up to join our network."); ?></p>

                    <?php $form = ActiveForm::begin(['id' => 'invite-form']); ?>
                    <?= $form->field($invite, 'email')->input('email', ['id' => 'register-email', 'placeholder' => $invite->getAttributeLabel('email'), 'aria-label' => $invite->getAttributeLabel('email'), 'class' => 'form-control input-login'])->label(false); ?>
                    <?php if ($invite->showCaptureInRegisterForm()) : ?>
                        <div id="registration-form-captcha">
                            <div class='text-line'><?= Yii::t('UserModule.auth', 'Please enter letters from the image.'); ?></div>

                            <?= $form->field($invite, 'captcha')->widget(Captcha::class, [
                                'captchaAction' => '/user/auth/captcha',
                            ])->label(false); ?>
                        </div>
                    <?php endif; ?>

                    <div class="row btn-login-container">
                        <?= Html::submitButton(Yii::t('UserModule.auth', 'Register'), ['id' => 'login-button', 'data-ui-loader' => "", 'class' => 'btn btn-large btn-login']); ?>
                    </div>

                    <div class="row question-container">
                        <p><?= Yii::t('UserModule.auth', "Already have an account?"); ?></p>
                        <a id="password-recovery-link" href="<?= Url::toRoute('/user/auth/login'); ?>" data-pjax-prevent><?= Yii::t('UserModule.auth', "Sign in") ?></a>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

        <?php endif; ?>
    <?php endif; ?>
</div>

<script <?= Html::nonce() ?>>
    // Change border color after wrong validation
    <?php if ($model->hasErrors()) { ?>
        $('#login_username').addClass('border-error');
    <?php } ?>

    // Change border color after wrong validation
    <?php if ($invite->hasErrors()) { ?>
        $('#invite-captcha').addClass('border-error');
    <?php } ?>

    <?php if ($invite->showCaptureInRegisterForm()) { ?>
        $('#register-email').on('focus', function() {
            $('#registration-form-captcha').fadeIn(500);
        });
    <?php } ?>
</script>
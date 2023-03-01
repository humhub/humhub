<?php

use humhub\widgets\SiteLogo;
use yii\bootstrap\ActiveForm;
use humhub\modules\user\widgets\AuthChoice;
use humhub\libs\Html;
use humhub\modules\user\models\Invite;
use yii\captcha\Captcha;

/* @var $invite Invite */

$this->pageTitle = Yii::t('UserModule.auth', 'Create Account');
?>

<div class="container" style="text-align: center;">
    <?= SiteLogo::widget(['place' => 'login']) ?>
    <br/>
    <div class="row">
        <div id="create-account-form" class="panel panel-default animated bounceIn"
             style="max-width: 500px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading">
                <?= Yii::t('UserModule.auth', '<strong>Account</strong> registration') ?>
            </div>
            <div class="panel-body">
                <?php if (Yii::$app->session->hasFlash('error')): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= Yii::$app->session->getFlash('error') ?>
                    </div>
                <?php endif; ?>

                <?php $form = ActiveForm::begin(['id' => 'registration-form']); ?>
                <?= $form->field($invite, 'email')->input('email', ['id' => 'register-email', 'placeholder' => $invite->getAttributeLabel('email'), 'aria-label' => $invite->getAttributeLabel('email')])->label(false); ?>
                <?php if ($invite->showCaptureInRegisterForm()) : ?>
                    <div id="registration-form-captcha" style="display: none;">
                        <div><?= Yii::t('UserModule.auth', 'Please enter the letters from the image.'); ?></div>

                        <?= $form->field($invite, 'captcha')->widget(Captcha::class, [
                            'captchaAction' => '/user/auth/captcha',
                        ])->label(false); ?>
                    </div>
                <?php endif; ?>
                <hr>
                <?= Html::submitButton(Yii::t('UserModule.auth', 'Register'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

    <?= humhub\widgets\LanguageChooser::widget(); ?>
</div>

<script <?= Html::nonce() ?>>

    // Shake panel after wrong validation
    <?php if ($invite->hasErrors()) { ?>
    $('#create-account-form').removeClass('bounceInLeft');
    $('#create-account-form').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php } ?>

    <?php if ($invite->showCaptureInRegisterForm()) { ?>
    $('#register-email').on('focus', function () {
        $('#registration-form-captcha').fadeIn(500);
    });
    <?php } ?>

</script>



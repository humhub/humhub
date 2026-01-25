<?php

use humhub\helpers\Html;
use humhub\modules\user\models\Invite;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\CaptchaField;
use humhub\widgets\SiteLogo;

/**
 * @var $invite Invite
 * @var $showAuthClients bool
 * @var $showRegistrationForm bool
 */

$this->pageTitle = Yii::t('UserModule.auth', 'Create Account');
?>

<div id="user-registration-by-link" class="container<?= AuthChoice::getClientsCount() > 1 ? ' has-multiple-auth-buttons' : '' ?>">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br/>
    <div id="create-account-form" class="panel panel-default animated bounceIn"
         data-has-auth-client="0">
        <div class="panel-heading">
            <?= Yii::t('UserModule.auth', '<strong>Account</strong> registration') ?>
        </div>
        <div class="panel-body">
            <?php if ($showAuthClients && AuthChoice::hasClients()): ?>
                <?= AuthChoice::widget(['showOrDivider' => $showRegistrationForm]) ?>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <?php if ($showRegistrationForm): ?>
                <?php $form = ActiveForm::begin(['id' => 'registration-form']); ?>
                <?= $form->field($invite, 'email')->input('email', ['id' => 'register-email', 'placeholder' => $invite->getAttributeLabel('email'), 'aria-label' => $invite->getAttributeLabel('email')])->label(false); ?>
                <?php if ($invite->showCaptureInRegisterForm()) : ?>
                    <div id="registration-form-captcha" style="display: none;">
                        <?= $form->field($invite, 'captcha')->widget(CaptchaField::class)->label(false) ?>
                    </div>
                <?php endif; ?>

                <?= Html::submitButton(Yii::t('UserModule.auth', 'Register'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

                <?php ActiveForm::end(); ?>
            <?php endif; ?>
        </div>
    </div>

    <?= humhub\widgets\LanguageChooser::widget(['vertical' => true, 'hideLabel' => true]); ?>
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

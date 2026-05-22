<?php

use humhub\helpers\Html;
use humhub\modules\user\models\Invite;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\CaptchaField;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Sign Up');

/* @var $this \humhub\components\View */
/* @var $invite Invite */

?>

<div id="user-auth-register" class="container container-registration">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default animated bounceIn" id="register-form">

        <div class="panel-heading"><?= Yii::t('UserModule.auth', 'Sign Up') ?></div>

        <div class="panel-body pt-0">

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <p class="text-">
                <?= Yii::t('UserModule.auth', 'Enter your email address and click Send. We will email you a sign-up link to create your account.') ?>
            </p>

            <?php $form = ActiveForm::begin(['id' => 'invite-form']) ?>
                <?= $form->field($invite, 'email')->input('email', [
                    'id' => 'register-email',
                    'placeholder' => 'example@example.com',
                    'aria-label' => $invite->getAttributeLabel('email'),
                    'autocomplete' => 'email',
                    'autofocus' => true,
                ])->label(false) ?>

                <?php if ($invite->showCaptureInRegisterForm()): ?>
                    <?= $form->field($invite, 'captcha')
                        ->widget(CaptchaField::class, ['showOnFocusElement' => '#register-email'])
                        ->label(false) ?>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-6">
                        <?= Button::light(Yii::t('UserModule.auth', 'Back'))
                            ->link(Url::to(['/user/auth/login']))
                            ->cssClass('w-100')
                            ->pjax(false) ?>
                    </div>
                    <div class="col-6">
                        <?= Button::save(Yii::t('UserModule.auth', 'Send'))
                            ->submit()
                            ->cssClass('w-100') ?>
                    </div>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <br>

    <?= LanguageChooser::widget(['vertical' => true, 'hideLabel' => true]) ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        $('#register-email').focus();
    });

    <?php if ($invite->hasErrors()) { ?>
    $('#register-form').removeClass('bounceIn').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php } ?>
</script>

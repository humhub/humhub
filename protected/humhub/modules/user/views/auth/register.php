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

        <div class="panel-heading"><?= Yii::t('UserModule.auth', 'Sign up') ?></div>

        <div class="panel-body">

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <p>
                <?= Yii::t('UserModule.auth', 'To Sign Up, enter your email and we will send you a sign up link.') ?>
            </p>

            <?php $form = ActiveForm::begin(['id' => 'invite-form']) ?>
                <?= $form->field($invite, 'email')->input('email', [
                    'id' => 'register-email',
                    'placeholder' => $invite->getAttributeLabel('email'),
                    'aria-label' => $invite->getAttributeLabel('email'),
                    'autocomplete' => 'email',
                    'autofocus' => true,
                ])->label(false) ?>

                <?php if ($invite->showCaptureInRegisterForm()): ?>
                    <?= $form->field($invite, 'captcha')
                        ->widget(CaptchaField::class, ['showOnFocusElement' => '#register-email'])
                        ->label(false) ?>
                <?php endif; ?>

                <?= Html::submitButton(
                    Yii::t('UserModule.auth', 'Send'),
                    ['class' => 'btn btn-primary w-100', 'data-ui-loader' => ''],
                ) ?>

                <?= Button::light(Yii::t('UserModule.auth', 'Back'))
                    ->link(Url::to(['/user/auth/login']))
                    ->cssClass('w-100 mt-2')
                    ->pjax(false) ?>
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

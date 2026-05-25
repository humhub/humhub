<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\user\models\forms\LoginIdentity;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Sign In');

/* @var $this View */
/* @var $model LoginIdentity */
/* @var $signUpAllowed bool */
/* @var $showLoginForm bool */

?>

<div id="user-auth-login" class="container container-login">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default mb-4 animated bounceIn" id="login-form">
        <div class="panel-heading">
            <strong class="fw-bolder"><?= Yii::t('UserModule.auth', 'Sign In') ?></strong>
        </div>
        <div class="panel-body pt-0">
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <?= Alert::danger(Yii::$app->session->getFlash('error'))->closeButton(false) ?>
            <?php endif; ?>

            <?php if ($showLoginForm): ?>
                <p class="mb-2"><?= $model->getAttributeLabel('username') ?></p>
                <?php $form = ActiveForm::begin(['id' => 'account-login-form', 'enableClientValidation' => false]) ?>
                    <?= $form->field($model, 'username')->textInput([
                        'id' => 'login_username',
                        'placeholder' => $model->getAttributeLabel('username'),
                        'aria-label' => $model->getAttributeLabel('username'),
                        'autocomplete' => 'username',
                        'autofocus' => true,
                    ])->label(false) ?>

                    <?= Html::submitButton(
                        Yii::t('UserModule.auth', 'Continue'),
                        ['id' => 'continue-button', 'data-ui-loader' => '', 'class' => 'btn btn-large btn-primary w-100'],
                    ) ?>

                    <?php if ($signUpAllowed): ?>
                        <?= Button::light(Yii::t('UserModule.auth', 'Sign Up'))
                            ->link(Url::to(['/user/auth/register']))
                            ->cssClass('w-100 mt-2')
                            ->id('register-button')
                            ->pjax(false) ?>
                    <?php endif; ?>
                <?php ActiveForm::end() ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (AuthChoice::hasClients()): ?>
        <div class="panel panel-default mb-4 animated bounceIn" id="auth-choice-panel">
            <div class="panel-body">
                <p class="text-center mb-2">
                    <?= Yii::t('UserModule.auth', 'Or continue with') ?>
                </p>
                <?= AuthChoice::widget() ?>
            </div>
        </div>
    <?php endif; ?>

    <?= LanguageChooser::widget(['vertical' => true, 'hideLabel' => true]) ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        $('#login_username').focus();
    });

    <?php if ($model->hasErrors()) { ?>
    $('#login-form').removeClass('bounceIn').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php } ?>
</script>

<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\LoginIdentity;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Login');

/* @var $this \humhub\components\View */
/* @var $model LoginIdentity */
/* @var $signUpAllowed bool */
/* @var $showLoginForm bool */

?>

<div id="user-auth-login" class="container container-login">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default animated bounceIn" id="login-form">

        <div class="panel-heading"><?= Yii::t('UserModule.auth', 'Please sign in') ?></div>

        <div class="panel-body">

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <?php if ($showLoginForm): ?>
                <?php if ($signUpAllowed): ?>
                    <p><?= Yii::t('UserModule.auth', "If you're already a member, please login with your username/email and password.") ?></p>
                <?php else: ?>
                    <p><?= Yii::t('UserModule.auth', "Please login with your username/email and password.") ?></p>
                <?php endif; ?>

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
                <?php ActiveForm::end(); ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (AuthChoice::hasClients()): ?>
        <br>
        <div class="panel panel-default animated bounceIn" id="auth-choice-panel">
            <div class="panel-body">
                <p class="text-center mb-2">
                    <?= Yii::t('UserModule.auth', 'Or continue with') ?>
                </p>
                <?= AuthChoice::widget() ?>
            </div>
        </div>
    <?php endif; ?>

    <br>

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

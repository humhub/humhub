<?php

use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\Invite;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use humhub\modules\user\widgets\AuthChoice;

/* @var $canRegister boolean */
/* @var $model Login */
/* @var $invite Invite */
/* @var $info string */
?>
<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?= Yii::t('UserModule.auth', '<strong>Join</strong> the network'); ?></h4>
        </div>
        <div class="modal-body">
            <br/>

            <?php if ($canRegister) : ?>
                <div class="text-center">
                    <ul id="tabs" class="nav nav-tabs tabs-center" data-tabs="tabs">
                        <li class="<?= (!isset($_POST['Invite'])) ? "active" : ""; ?> tab-login"><a
                                href="#login"
                                data-toggle="tab"><?= Yii::t('SpaceModule.base', 'Login'); ?></a>
                        </li>
                        <li class="<?= (isset($_POST['Invite'])) ? "active" : ""; ?> tab-register"><a
                                href="#register"
                                data-toggle="tab"><?= Yii::t('SpaceModule.base', 'New user?'); ?></a>
                        </li>
                    </ul>
                </div>
                <br/>
            <?php endif; ?>


            <div class="tab-content">
                <div class="tab-pane <?= (!isset($_POST['Invite'])) ? "active" : ""; ?>" id="login">

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>

                   <?php if (AuthChoice::hasClients()): ?>
                        <?= AuthChoice::widget([]) ?>
                    <?php else: ?>
                        <?php if ($canRegister) : ?>
                            <p><?= Yii::t('UserModule.auth', "If you're already a member, please login with your username/email and password."); ?></p>
                        <?php else: ?>
                            <p><?= Yii::t('UserModule.auth', "Please login with your username/email and password."); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>
                    <?= $form->field($model, 'username')->textInput(['id' => 'login_username', 'placeholder' => Yii::t('UserModule.auth', 'username or email')]); ?>
                    <?= $form->field($model, 'password')->passwordInput(['id' => 'login_password', 'placeholder' => Yii::t('UserModule.auth', 'password')]); ?>
                    <?= $form->field($model, 'rememberMe')->checkbox(); ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <button href="#" id="login-button" data-ui-loader type="submit" class="btn btn-primary" data-action-click="ui.modal.submit" data-action-url="<?= Url::to(['/user/auth/login']) ?>">
                                <?= Yii::t('UserModule.auth', 'Sign in') ?>
                            </button>

                        </div>
                        <div class="col-md-8 text-right">
                            <small>
                                <a id="recoverPasswordBtn" href="#" data-action-click="ui.modal.load" data-action-url="<?= Url::to(['/user/password-recovery']) ?>">
                                    <?= Yii::t('UserModule.auth', 'Forgot your password?') ?>
                                </a>
                            </small>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>

                <?php if ($canRegister) : ?>
                    <div class="tab-pane <?= (isset($_POST['Invite'])) ? "active" : ""; ?>"
                         id="register">

                        <?php if (AuthChoice::hasClients()): ?>
                            <?= AuthChoice::widget() ?>
                        <?php else: ?>
                            <p><?= Yii::t('UserModule.auth', "Don't have an account? Join the network by entering your e-mail address."); ?></p>
                        <?php endif; ?>

                        <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

                        <?= $form->field($invite, 'email')->input('email', ['id' => 'register-email', 'placeholder' => Yii::t('UserModule.auth', 'email')]); ?>
                        <?php if ($invite->showCaptureInRegisterForm()) : ?>
                            <div><?= Yii::t('UserModule.auth', 'Please enter the letters from the image.'); ?></div>
                            <?= $form->field($invite, 'captcha')->widget(Captcha::class, [
                            'captchaAction' => '/user/auth/captcha',
                        ])->label(false); ?>
                        <?php endif; ?>
                        <hr>

                        <a href="#" class="btn btn-primary" data-ui-loader data-action-click="ui.modal.submit" data-action-url="<?= Url::to(['/user/auth/login']) ?>">
                            <?= Yii::t('UserModule.auth', 'Register') ?>
                        </a>

                        <?php ActiveForm::end(); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script <?= \humhub\libs\Html::nonce() ?>>
    $(document).on('humhub:ready', function () {
        $('#login_username').focus();
    });

    $('.tab-register a').on('shown.bs.tab', function (e) {
        $('#register-email').focus();
    })

    $('.tab-login a').on('shown.bs.tab', function (e) {
        $('#login_username').focus();
    })
</script>

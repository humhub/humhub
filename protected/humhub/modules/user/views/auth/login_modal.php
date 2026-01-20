<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\Invite;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\CaptchaField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $canRegister bool */
/* @var $model Login */
/* @var $invite Invite */
/* @var $info string */
/* @var $passwordRecoveryRoute string|array|null */
/* @var $showLoginForm bool */
/* @var $showRegistrationForm bool */

?>

<?php Modal::beginDialog([
    'id' => 'user-auth-login-modal',
    'title' => Yii::t('UserModule.auth', '<strong>Join</strong> the network'),
]) ?>
    <?php if ($canRegister && $showRegistrationForm) : ?>
        <div class="text-center">
            <ul id="tabs" class="nav nav-tabs tabs-center" data-tabs="tabs">
                <li class="nav-item tab-login">
                    <a
                        href="#login"
                        class="nav-link<?= !isset($_POST['Invite']) ? ' active' : '' ?>"
                        data-bs-toggle="tab"><?= Yii::t('SpaceModule.base', 'Login') ?></a>
                </li>
                <li class="nav-item tab-register">
                    <a
                        href="#register"
                        class="nav-link<?= (isset($_POST['Invite'])) ? ' active' : '' ?>"
                        data-bs-toggle="tab"><?= Yii::t('SpaceModule.base', 'New user?') ?></a>
                </li>
            </ul>
        </div>
        <br/>
    <?php endif; ?>

    <div class="tab-content">
        <div class="tab-pane <?= (!isset($_POST['Invite'])) ? "active" : "" ?>" id="login">

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <?php if (AuthChoice::hasClients()): ?>
                <?= AuthChoice::widget(['showOrDivider' => $showLoginForm]) ?>
            <?php else: ?>
                <?php if ($canRegister) : ?>
                    <p><?= Yii::t('UserModule.auth', "If you're already a member, please login with your username/email and password.") ?></p>
                <?php elseif ($showLoginForm): ?>
                    <p><?= Yii::t('UserModule.auth', "Please login with your username/email and password.") ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($showLoginForm): ?>
                <?php $form = ActiveForm::begin(['id' => 'account-login-form-modal', 'enableClientValidation' => false]); ?>
                <?= $form->field($model, 'username')->textInput(['id' => 'login_username', 'placeholder' => $model->getAttributeLabel('username')]) ?>
                <?= $form->field($model, 'password')->passwordInput(['id' => 'login_password', 'placeholder' => $model->getAttributeLabel('password')]) ?>
                <?= $model->hideRememberMe ? '' : $form->field($model, 'rememberMe')->checkbox() ?>
                <div class="modal-body-footer">
                    <div class="d-flex flex-column align-center-end w-100">
                        <?= ModalButton::save(Yii::t('UserModule.auth', 'Sign in'))->submit(['/user/auth/login'])
                            ->id('login-button')
                            ->cssClass('w-100') ?>
                        <?php if ($passwordRecoveryRoute) : ?>
                            <small>
                                <?= Html::a(
                                    Html::tag('br') . Yii::t('UserModule.auth', 'Forgot your password?'),
                                    $passwordRecoveryRoute,
                                    ArrayHelper::merge([
                                        'id' => 'recoverPasswordBtn',
                                    ], is_array($passwordRecoveryRoute) ? [
                                        'data' => [
                                            'action-click' => 'ui.modal.load',
                                            'action-url' => Url::to($passwordRecoveryRoute),
                                        ],
                                    ] : [
                                        'target' => '_blank',
                                    ]),
                                ) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            <?php endif; ?>
        </div>

        <?php if ($canRegister && $showRegistrationForm) : ?>
            <div class="tab-pane <?= (isset($_POST['Invite'])) ? "active" : "" ?>"
                 id="register">

                <?php if (AuthChoice::hasClients()): ?>
                    <?= AuthChoice::widget(['showOrDivider' => true]) ?>
                <?php else: ?>
                    <p><?= Yii::t('UserModule.auth', "Don't have an account? Join the network by entering your e-mail address.") ?></p>
                <?php endif; ?>

                <?php $form = ActiveForm::begin(['id' => 'invite-form-modal', 'enableClientValidation' => false]); ?>

                <?= $form->field($invite, 'email')->input('email', ['id' => 'register-email', 'placeholder' => $invite->getAttributeLabel('email')]) ?>
                <?php if ($invite->showCaptureInRegisterForm()) : ?>
                    <?= $form->field($invite, 'captcha')->widget(CaptchaField::class)->label(false) ?>
                <?php endif; ?>

                <div class="modal-body-footer">
                    <?= ModalButton::save(Yii::t('UserModule.auth', 'Register'))->submit(['/user/auth/login']) ?>
                </div>

                <?php ActiveForm::end(); ?>

            </div>
        <?php endif; ?>
    </div>

<?php Modal::endDialog() ?>

<script <?= Html::nonce() ?>>
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

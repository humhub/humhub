<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\LoginIdentity;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $model LoginIdentity */
/* @var $signUpAllowed bool */
/* @var $showLoginForm bool */

?>

<?php Modal::beginDialog([
    'id' => 'user-auth-login-modal',
    'title' => Yii::t('UserModule.auth', 'Sign In'),
]) ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <?= Alert::danger(Yii::$app->session->getFlash('error'))->closeButton(false) ?>
    <?php endif; ?>

    <?php if ($showLoginForm): ?>
        <?php $form = ActiveForm::begin(['id' => 'account-login-form-modal', 'enableClientValidation' => false]) ?>
            <p class="mb-2"><?= $model->getAttributeLabel('username') ?></p>
            <?= $form->field($model, 'username')->textInput([
                'id' => 'login_username',
                'placeholder' => $model->getAttributeLabel('username'),
                'autocomplete' => 'username',
            ])->label(false) ?>

            <?= ModalButton::save(Yii::t('UserModule.auth', 'Continue'))
                ->submit(['/user/auth/login'])
                ->id('continue-button')
                ->cssClass('w-100') ?>

            <?php if ($signUpAllowed): ?>
                <?= ModalButton::light(Yii::t('UserModule.auth', 'Sign Up'))
                    ->load(['/user/auth/register'])
                    ->cssClass('w-100 mt-2')
                    ->id('register-button-modal') ?>
            <?php endif; ?>
        <?php ActiveForm::end() ?>
    <?php endif; ?>

    <?php if (AuthChoice::hasClients()): ?>
        <div class="mt-3">
            <?php if ($showLoginForm): ?>
                <p class="text-center mb-2">
                    <?= Yii::t('UserModule.auth', 'Or continue with') ?>
                </p>
            <?php endif; ?>
            <?= AuthChoice::widget() ?>
        </div>
    <?php endif; ?>

<?php Modal::endDialog() ?>

<script <?= Html::nonce() ?>>
    $(document).on('humhub:ready', function () {
        $('#login_username').focus();
    });
</script>

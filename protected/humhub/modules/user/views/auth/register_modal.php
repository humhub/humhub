<?php

use humhub\helpers\Html;
use humhub\modules\user\models\Invite;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\CaptchaField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $invite Invite */
?>

<?php Modal::beginDialog([
    'id' => 'user-auth-register-modal',
    'title' => Yii::t('UserModule.auth', 'Sign Up'),
]) ?>

    <p><?= Yii::t('UserModule.auth', 'Enter your email address and click Send. We will email you a sign-up link to create your account.') ?></p>

    <?php $form = ActiveForm::begin(['id' => 'invite-form-modal', 'enableClientValidation' => false]) ?>

        <?= $form->field($invite, 'email')->input('email', [
            'id' => 'register-email',
            'placeholder' => 'example@example.com',
            'autocomplete' => 'email',
        ])->label(false) ?>

        <?php if ($invite->showCaptureInRegisterForm()): ?>
            <?= $form->field($invite, 'captcha')
                ->widget(CaptchaField::class, ['showOnFocusElement' => '#register-email'])
                ->label(false) ?>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-6">
                <?= ModalButton::light(Yii::t('UserModule.auth', 'Back'))
                    ->load(['/user/auth/login', 'forget' => 1])
                    ->cssClass('w-100') ?>
            </div>
            <div class="col-6">
                <?= ModalButton::save(Yii::t('UserModule.auth', 'Send'))
                    ->submit(['/user/auth/register'])
                    ->cssClass('w-100') ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>

<?php Modal::endDialog() ?>

<script <?= Html::nonce() ?>>
    $(document).on('humhub:ready', function () {
        $('#register-email').focus();
    });
</script>

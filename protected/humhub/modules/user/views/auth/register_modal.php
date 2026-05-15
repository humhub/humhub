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
    'title' => Yii::t('UserModule.auth', 'Sign up'),
]) ?>

    <p><?= Yii::t('UserModule.auth', 'To Sign Up, enter your email and we will send you a sign up link.') ?></p>

    <?php $form = ActiveForm::begin(['id' => 'invite-form-modal', 'enableClientValidation' => false]) ?>

        <?= $form->field($invite, 'email')->input('email', [
            'id' => 'register-email',
            'placeholder' => $invite->getAttributeLabel('email'),
            'autocomplete' => 'email',
        ]) ?>

        <?php if ($invite->showCaptureInRegisterForm()): ?>
            <?= $form->field($invite, 'captcha')
                ->widget(CaptchaField::class, ['showOnFocusElement' => '#register-email'])
                ->label(false) ?>
        <?php endif; ?>

        <div class="modal-body-footer">
            <div class="d-flex flex-column w-100 gap-2">
                <?= ModalButton::save(Yii::t('UserModule.auth', 'Send'))
                    ->submit(['/user/auth/register'])
                    ->cssClass('w-100') ?>

                <?= ModalButton::light(Yii::t('UserModule.auth', 'Back'))
                    ->load(['/user/auth/login'])
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

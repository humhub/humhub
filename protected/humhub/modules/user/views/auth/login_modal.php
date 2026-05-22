<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\LoginIdentity;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $model LoginIdentity */
/* @var $signUpAllowed bool */
/* @var $showLoginForm bool */

?>

<?php Modal::beginDialog([
    'id' => 'user-auth-login-modal',
    'title' => Yii::t('UserModule.auth', 'Please sign in'),
]) ?>

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

        <?php $form = ActiveForm::begin(['id' => 'account-login-form-modal', 'enableClientValidation' => false]) ?>
            <?= $form->field($model, 'username')->textInput([
                'id' => 'login_username',
                'placeholder' => $model->getAttributeLabel('username'),
                'autocomplete' => 'username',
            ]) ?>

            <div class="modal-body-footer">
                <div class="d-flex flex-column align-center-end w-100 gap-2">
                    <?= ModalButton::save(Yii::t('UserModule.auth', 'Continue'))
                        ->submit(['/user/auth/login'])
                        ->id('continue-button')
                        ->cssClass('w-100') ?>

                    <?php if ($signUpAllowed): ?>
                        <?= ModalButton::light(Yii::t('UserModule.auth', 'Sign Up'))
                            ->load(['/user/auth/register'])
                            ->cssClass('w-100')
                            ->id('register-button-modal') ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
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

<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\AccountRecoverPassword;
use humhub\widgets\form\CaptchaField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/**
 * @var $model AccountRecoverPassword
 */

?>

<?php $form = Modal::beginFormDialog([
    'id' => 'user-password-recovery-modal',
    'title' => Yii::t('UserModule.auth', 'Password recovery'),
]) ?>
    <p><?= Yii::t('UserModule.auth', 'Just enter your e-mail address. We\'ll send you recovery instructions!') ?></p>
    <?= $form->field($model, 'email')
        ->textInput([
            'id' => 'email_txt',
            'placeholder' => Yii::t('UserModule.auth', 'Your email'),
        ])
        ->label(false) ?>
    <?= $form->field($model, 'captcha')
        ->widget(CaptchaField::class, ['showOnFocusElement' => '#email_txt'])
        ->label(false) ?>

    <div class="row g-3">
        <div class="col-6">
            <?= ModalButton::light(Yii::t('UserModule.auth', 'Back'))
                ->load(['/user/auth/login'])
                ->cssClass('w-100') ?>
        </div>
        <div class="col-6">
            <?= ModalButton::save(Yii::t('UserModule.auth', 'Reset password'))
                ->submit(['/user/password-recovery'])
                ->cssClass('w-100') ?>
        </div>
    </div>
<?php Modal::endFormDialog() ?>


<script <?= Html::nonce() ?>>
    <?php if ($model->hasErrors()): ?>
    $('#password-recovery-form').removeClass('bounceIn');
    $('#password-recovery-form').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php endif; ?>
</script>

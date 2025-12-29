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
    'title' => Yii::t('UserModule.auth', '<strong>Password</strong> recovery'),
    'footer'
        => ModalButton::light(Yii::t('UserModule.auth', 'Back'))->load(['/user/auth/login'])->pjax(false) . ' '
        . ModalButton::save(Yii::t('UserModule.auth', 'Reset password'))->submit(['/user/password-recovery']),
]) ?>
    <p><?= Yii::t('UserModule.auth', 'Just enter your e-mail address. We\'ll send you recovery instructions!') ?></p>
    <?= $form->field($model, 'email')->textInput(['id' => 'email_txt', 'placeholder' => Yii::t('UserModule.auth', 'Your email')]) ?>
    <?= $form->field($model, 'captcha')->widget(CaptchaField::class)->label(false) ?>
<?php Modal::endFormDialog() ?>


<script <?= Html::nonce() ?>>
    <?php if ($model->hasErrors()): ?>
    $('#password-recovery-form').removeClass('bounceIn');
    $('#password-recovery-form').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php endif; ?>
</script>

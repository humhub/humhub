<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\AccountRecoverPassword;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\Modal;
use yii\captcha\Captcha;
use yii\helpers\Url;

/**
 * @var $model AccountRecoverPassword
 */

?>

<?php $form = ActiveForm::begin() ?>

    <?php Modal::beginDialog([
        'title' => Yii::t('UserModule.auth', '<strong>Password</strong> recovery'),
    ]) ?>

        <p><?= Yii::t('UserModule.auth', 'Just enter your e-mail address. We\'ll send you recovery instructions!'); ?></p>

        <div class="mb-3">
            <?= $form->field($model, 'email')->textInput(['id' => 'email_txt', 'placeholder' => Yii::t('UserModule.auth', 'Your email')]); ?>
        </div>

        <div class="mb-3">
            <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                'model' => $model,
                'attribute' => 'verifyCode',
                'captchaAction' => '/user/auth/captcha',
                'options' => ['class' => 'form-control', 'placeholder' => Yii::t('UserModule.auth', 'Enter security code above')],
            ])->label(false) ?>
        </div>

        <hr>

        <a href="#" class="btn btn-primary" data-action-click="ui.modal.submit"
           data-action-url="<?= Url::to(['/user/password-recovery']) ?>" data-ui-loader>
            <?= Yii::t('UserModule.auth', 'Reset password') ?>
        </a>
        &nbsp;
        <a href="#" class="btn btn-light" data-action-click="ui.modal.load"
           data-action-url="<?= Url::to(['/user/auth/login']) ?>" data-ui-loader>
            <?= Yii::t('UserModule.auth', 'Back') ?>
        </a>

    <?php Modal::endDialog() ?>

<?php $form::end() ?>


<script <?= Html::nonce() ?>>
    <?php if ($model->hasErrors()): ?>
    $('#password-recovery-form').removeClass('bounceIn');
    $('#password-recovery-form').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php endif; ?>
</script>

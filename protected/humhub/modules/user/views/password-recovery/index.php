<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\AccountRecoverPassword;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\CaptchaField;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Password recovery');

/**
 * @var $model AccountRecoverPassword
 */

?>
<div id="user-password-recovery" class="container">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div id="password-recovery-form" class="panel panel-default animated bounceIn">
        <div class="panel-heading">
            <?= Yii::t('UserModule.auth', '<strong>Password</strong> recovery') ?>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

            <p><?= Yii::t('UserModule.auth', 'Just enter your e-mail address. We\'ll send you recovery instructions!'); ?></p>

            <?= $form->field($model, 'email')->textInput(['class' => 'form-control', 'id' => 'email_txt', 'placeholder' => Yii::t('UserModule.auth', 'Your email')])->label(false) ?>

            <div class="mb-3">
                <?= $form->field($model, 'captcha')->widget(CaptchaField::class)->label(false) ?>
            </div>

            <?= Button::light(Yii::t('UserModule.auth', 'Back'))->link(Url::home())->pjax(false) ?>
            <?= Html::submitButton(Yii::t('UserModule.auth', 'Reset password'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= LanguageChooser::widget(['vertical' => true, 'hideLabel' => true]) ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        // set cursor to email field
        $('#email_txt').focus();
    });

    // Shake panel after wrong validation
    <?php if ($model->hasErrors()) : ?>
    $('#password-recovery-form').removeClass('bounceIn');
    $('#password-recovery-form').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php endif; ?>
</script>

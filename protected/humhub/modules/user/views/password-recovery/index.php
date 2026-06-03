<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\AccountRecoverPassword;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\CaptchaField;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;

$this->pageTitle = Yii::t('UserModule.auth', 'Password recovery');

/**
 * @var $model AccountRecoverPassword
 */

?>
<div id="user-password-recovery" class="container container-password">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div id="password-recovery-form" class="panel panel-default mb-4 animated bounceIn">
        <div class="panel-heading">
            <strong class="fw-bolder"><?= Yii::t('UserModule.auth', 'Password recovery') ?></strong>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

            <p><?= Yii::t('UserModule.auth', 'Just enter your e-mail address. We\'ll send you recovery instructions!'); ?></p>

            <?= $form->field($model, 'email')->textInput(['class' => 'form-control', 'id' => 'email_txt', 'placeholder' => Yii::t('UserModule.auth', 'Your email')])->label(false) ?>

            <div class="mb-3">
                <?= $form->field($model, 'captcha')
                    ->widget(CaptchaField::class, ['showOnFocusElement' => '#email_txt'])
                    ->label(false) ?>
            </div>

            <div class="row g-3">
                <div class="col-6">
                    <?= Button::light(Yii::t('UserModule.auth', 'Back'))
                        ->link(['/user/auth/password'])
                        ->cssClass('w-100')
                        ->pjax(false) ?>
                </div>
                <div class="col-6">
                    <?= Button::save(Yii::t('UserModule.auth', 'Reset password'))
                        ->submit()
                        ->cssClass('w-100') ?>
                </div>
            </div>

            <?php ActiveForm::end() ?>
        </div>
    </div>

    <?= LanguageChooser::widget(['vertical' => true]) ?>
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

<?php

use humhub\helpers\Html;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Password reset');
?>
<div id="user-password-recovery-reset" class="container container-password">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div id="password-recovery-form" class="panel panel-default animated bounceIn">
        <div class="panel-heading">
            <strong class="fw-bolder"><?= Yii::t('UserModule.auth', 'Change your password') ?></strong>
        </div>
        <div class="panel-body">

            <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

            <?= $form->field($model, 'newPassword')->passwordInput(['class' => 'form-control', 'id' => 'new_password', 'maxlength' => 255, 'value' => '']) ?>

            <?= $form->field($model, 'newPasswordConfirm')->passwordInput(['class' => 'form-control', 'maxlength' => 255, 'value' => '']) ?>

            <div class="row g-3">
                <div class="col-6">
                    <?= Button::light(Yii::t('UserModule.auth', 'Back'))
                        ->link(Url::home())
                        ->cssClass('w-100')
                        ->pjax(false) ?>
                </div>
                <div class="col-6">
                    <?= Button::save(Yii::t('UserModule.auth', 'Change password'))
                        ->submit()
                        ->cssClass('w-100') ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= LanguageChooser::widget(['vertical' => true]) ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        // set cursor to email field
        $('#new_password').focus();
    });

    // Shake panel after wrong validation
    <?php if ($model->hasErrors()) { ?>
    $('#password-recovery-form').removeClass('bounceIn');
    $('#password-recovery-form').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php } ?>
</script>

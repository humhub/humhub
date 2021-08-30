<?php

use humhub\modules\user\models\Password;
use humhub\widgets\Button;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use humhub\widgets\SiteLogo;

/* @var $model Password */

$this->pageTitle = Yii::t('UserModule.auth', 'Change password');
?>
<div class="container" style="text-align: center;">
    <?= SiteLogo::widget(['place' => 'login']); ?>
    <br>

    <div class="row">
        <div id="must-change-password-form" class="panel panel-default animated bounceIn"
             style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?= Yii::t('UserModule.auth', '<strong>Change</strong> Password'); ?></div>
            <div class="panel-body">

                <?php $form = ActiveForm::begin(); ?>

                <p><?= Yii::t('UserModule.auth', 'Due to security reasons you are required to change your password in order to access the platform.'); ?></p>

                <?= $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
                <hr>

                <?= $form->field($model, 'newPassword')->passwordInput(['maxlength' => 45]); ?>
                <?= $form->field($model, 'newPasswordConfirm')->passwordInput(['maxlength' => 45]); ?>

                <hr>
                <?= Html::submitButton(Yii::t('UserModule.auth', 'Confirm'), ['class' => 'btn btn-primary pull-left', 'data-ui-loader' => ""]); ?>

                <?php ActiveForm::end(); ?>

                <?= Button::danger(Yii::t('UserModule.auth', 'Log out'))->link(Url::toRoute('/user/auth/logout'), false)->options(['data-method' => 'POST'])->right() ?>

            </div>
        </div>
    </div>
</div>

<script <?= \humhub\libs\Html::nonce() ?>>
    $(function () {
        // set cursor to current password field
        $('#password-currentpassword').focus();
    });
</script>

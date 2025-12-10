<?php

use humhub\helpers\Html;
use humhub\modules\user\models\Password;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

/* @var $model Password */

$this->pageTitle = Yii::t('UserModule.auth', 'Change password');
?>
<div id="user-must-change-password" class="container">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div id="must-change-password-form" class="panel panel-default animated bounceIn">
        <div class="panel-heading"><?= Yii::t('UserModule.auth', '<strong>Change</strong> Password'); ?></div>
        <div class="panel-body">

            <?php $form = ActiveForm::begin(); ?>

            <p><?= Yii::t('UserModule.auth', 'Due to security reasons you are required to change your password in order to access the platform.'); ?></p>


            <?php if ($model->isAttributeSafe('currentPassword')): ?>
                <?= $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
                <hr>
            <?php endif; ?>

            <?= $form->field($model, 'newPassword')->passwordInput(['maxlength' => 45]); ?>
            <?= $form->field($model, 'newPasswordConfirm')->passwordInput(['maxlength' => 45]); ?>

            <hr>
            <?= Button::primary(Yii::t('UserModule.auth', 'Confirm'))->submit()->left() ?>

            <?php ActiveForm::end(); ?>

            <?= Button::light(Yii::t('UserModule.auth', 'Log out'))->link(Url::toRoute('/user/auth/logout'), false)->options(['data-method' => 'POST'])->right() ?>

        </div>
    </div>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        // set cursor to current password field
        $('#password-currentpassword').focus();
    });
</script>

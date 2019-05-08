<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CActiveForm;

$this->pageTitle = Yii::t('UserModule.views_auth_resetPassword', 'Password reset');
?>

<a class="brand" href="/dashboard"><img src="http://coinsence.localhost/uploads/logo_image/logo.png?cacheId=0"></a>

<div class="content">

    <div class="password-recovery-content col-md-4" id="password-recovery-form">

        <h1><?= Yii::t('UserModule.views_auth_resetPassword', 'Change your password'); ?></h1>

        <?php $form = CActiveForm::begin(['enableClientValidation'=>false]); ?>

        <?= $form->field($model, 'newPassword')
        ->passwordInput(['class' => 'form-control', 'id' => 'new_password', 'maxlength' => 255, 'value' => '', 'placeholder' => 'New password'])
        ->label(false)?>

        <?= $form->field($model, 'newPasswordConfirm')
            ->passwordInput(['class' => 'form-control', 'maxlength' => 255, 'value' => '', 'placeholder' => 'New Password Confirm'])
            ->label(false)?>

        <div class="row">
            <div class="col-md-12">
                <?= Html::submitButton(Yii::t('UserModule.views_auth_resetPassword', 'Change password'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>
            </div>
            <div class="col-md-12">
                <a data-ui-loader href="<?php echo Url::home() ?>"><?= Yii::t('UserModule.views_auth_resetPassword', 'Back') ?></a>
            </div>
        </div>

        <?php CActiveForm::end(); ?>

    </div>

</div>

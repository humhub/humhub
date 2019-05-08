<?php

use yii\captcha\Captcha;
use \yii\helpers\Url;
use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;
use humhub\modules\user\widgets\AuthChoice;

$this->pageTitle = Yii::t('UserModule.views_auth_login', 'Login');
?>



<a class="brand" href="/dashboard"><img src="http://coinsence.localhost/uploads/logo_image/logo.png?cacheId=0"></a>

<div class="content">

    <div class="bg"></div>

    <div class="login-content" id="login-form">

        <h1><?= Yii::t('UserModule.views_auth_login', 'Welcome back!'); ?></h1>
        <h5><?= Yii::t('UserModule.views_auth_login', 'Enter your details below'); ?></h5>

        <?php $form = ActiveForm::begin(['id' => 'account-login-form', 'enableClientValidation' => false, 'options' => [ 'class' => 'col-md-10' ]]); ?>
        <?= $form->field($model, 'username')->textInput(['id' => 'login_username', 'placeholder' => $model->getAttributeLabel('username'), 'aria-label' => $model->getAttributeLabel('username')])->label(false); ?>
        <?= $form->field($model, 'password')->passwordInput(['id' => 'login_password', 'placeholder' => $model->getAttributeLabel('password'), 'aria-label' => $model->getAttributeLabel('password')])->label(false); ?>
        <?= $form->field($model, 'rememberMe',  [ 'template' => '
        {input}{label}{error}
        <a id="password-recovery-link" class="forgot" href="' . Url::toRoute('/user/password-recovery') . '" data-pjax-prevent>' . Yii::t('UserModule.views_auth_login', 'Forgot your password?') .'</a>
        
        ' ])->checkbox(); ?>

        <div class="links row">
            <div class="col-md-12">
                <?= CHtml::submitButton(Yii::t('UserModule.views_auth_login', 'Log in'), ['id' => 'login-button', 'data-ui-loader' => "", 'class' => 'btn btn-large btn-primary']); ?>
            </div>
            <div class="col-md-12">
                <small>
                    <?= Yii::t('UserModule.views_auth_login', 'Don\'t have an account?') ?> <a href="<?= Url::toRoute('/user/auth/register'); ?>" data-pjax-prevent><?= Yii::t('UserModule.views_auth_login', 'Sign up') ?></a>
                </small>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>

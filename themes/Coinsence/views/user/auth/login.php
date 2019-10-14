<?php

use yii\captcha\Captcha;
use \yii\helpers\Url;
use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;
use humhub\modules\user\widgets\AuthChoice;

$this->pageTitle = Yii::t('UserModule.views_auth_login', 'Login');
?>



<a class="brand" href="/dashboard"><img class="white" src="<?= $this->theme->getBaseUrl(); ?>/img/slogan_white.png"></a>
<a class="brand" href="/dashboard"><img class="black" src="<?= $this->theme->getBaseUrl(); ?>/img/slogan_black.png"></a>

<div class="content">

    <div class="bg"></div>

    <div class="login-content" id="login-form">

        <h1><?= Yii::t('UserModule.views_auth_login', 'Welcome back!'); ?></h1>
        <h5><?= Yii::t('UserModule.views_auth_login', 'Enter your details below'); ?></h5>

        <?php $form = ActiveForm::begin(['id' => 'account-login-form', 'enableClientValidation' => false, 'options' => [ 'class' => 'col-xs-12' ]]); ?>
        <?= $form->field($model, 'username')->textInput(['id' => 'login_username', 'placeholder' => $model->getAttributeLabel('username'), 'aria-label' => $model->getAttributeLabel('username')])->label(false); ?>
        <?= $form->field($model, 'password')->passwordInput(['id' => 'login_password', 'placeholder' => $model->getAttributeLabel('password'), 'aria-label' => $model->getAttributeLabel('password')])->label(false); ?>
        <?= $form->field($model, 'rememberMe',  [ 'template' => '
        {input}{label}{error}
        <a id="password-recovery-link" class="forgot" href="' . Url::toRoute('/user/password-recovery') . '" data-pjax-prevent>' . Yii::t('UserModule.views_auth_login', 'Forgot your password?') .'</a>
        
        ' ])->checkbox(); ?>

        <div class="links row">
            <div class="col-md-12">
                <?= CHtml::submitButton(Yii::t('UserModule.views_auth_login', 'Log in'), ['id' => 'login-button', 'data-ui-loader' => "", 'class' => 'btn']); ?>
            </div>
            <div class="col-md-12">
                <h5>
                    <?= Yii::t('UserModule.views_auth_login', 'Don\'t have an account?') ?> <strong><a href="<?= Url::toRoute('/user/auth/register'); ?>" data-pjax-prevent><?= Yii::t('UserModule.views_auth_login', 'Sign up') ?></a></strong>
                </h5>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

        <div class="labels">
            <h6>By clicking the button, you agree to our <a href="#">Terms of services</a> and have read and acknowledge our <a href="#">Privacy Policy</a></h6>
        </div>

    </div>

</div>

<script type="text/javascript">

    $(function () {
        // set cursor to login field
        $('#login_username').focus();
    });

    const $password_toggler = $('<img>', {class: 'toggler', src: '<?= $this->theme->getBaseUrl(); ?>/img/eye.svg'});

    $('input[type="password"]').each(function (index, elem) {
        $(elem).parent().css('position', 'relative');
        $(elem).parent().prepend($password_toggler.clone());
    });

    $('.form-group').on('click', '.toggler', function () {
        const $password_field = $(this).siblings('input');
        const is_password = $password_field.attr('type') === 'password';

        $password_field.attr('type', is_password ? 'text': 'password');
    });

</script>

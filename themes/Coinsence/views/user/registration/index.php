<?php

use yii\helpers\Html;

$this->pageTitle = Yii::t('UserModule.views_auth_createAccount', 'Create Account');
?>

<a class="brand" href="/dashboard"><img src="<?= $this->theme->getBaseUrl(); ?>/img/slogan_black.png"></a>

<div class="content">

    <div class="create-account-content col-xs-11 col-md-6" id="create-account-form">

        <h1><?= Yii::t('UserModule.views_auth_createAccount', 'Welcome!'); ?></h1>
        <h5><?= Yii::t('UserModule.views_auth_createAccount', 'Create an account by entering your details below.'); ?></h5>

        <?php $form = \yii\bootstrap\ActiveForm::begin([ 'id' => 'registration-form', 'enableClientValidation' => false ]); ?>
        <?= $hForm->render($form); ?>
        <?php \yii\bootstrap\ActiveForm::end(); ?>

    </div>

    <div class="labels">
        <h6>By clicking the button, you agree to our <a href="#">Terms of services</a> and have read and acknowledge our <a href="#">Privacy Policy</a></h6>
    </div>

</div>

<script type="text/javascript">

    $(function () {
        // set cursor to login field
        $('#User_username').focus();
    });

    const $password_toggler = $('<img>', {class: 'toggler', src: '<?= $this->theme->getBaseUrl(); ?>/img/eye.svg'});

    $('input[type="password"]').each(function (index, elem) {
        $(elem).parent().css('position', 'relative');
        $(elem).parent().prepend($password_toggler.clone());
        console.log(elem);
    });

    $('.form-group').on('click', '.toggler', function () {
        const $password_field = $(this).siblings('input');
        const is_password = $password_field.attr('type') === 'password';

        $password_field.attr('type', is_password ? 'text': 'password');
    });

</script>

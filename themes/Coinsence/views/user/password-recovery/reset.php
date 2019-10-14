<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CActiveForm;

$this->pageTitle = Yii::t('UserModule.views_auth_resetPassword', 'Password reset');
?>

<a class="brand" href="/dashboard"><img src="http://coinsence.localhost/uploads/logo_image/logo.png?cacheId=0"></a>

<div class="content">

    <div class="password-recovery-content col-xs-11" id="password-recovery-form">

        <h1><?= Yii::t('UserModule.views_auth_resetPassword', 'Change your password'); ?></h1>
        <h5><?= Yii::t('UserModule.views_auth_resetPassword', 'Enter your new password'); ?></h5>

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

<script type="text/javascript">

    $(function () {
        // set cursor to email field
        $('#new_password').focus();
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

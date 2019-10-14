<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CActiveForm;

$this->pageTitle = Yii::t('UserModule.views_auth_recoverPassword', 'Password recovery');
?>

<a class="brand" href="/dashboard"><img src="<?= $this->theme->getBaseUrl(); ?>/img/slogan_black.png"></a>

<div class="content">

    <div class="password-recovery-content col-xs-11" id="password-recovery-form">

        <h1><?= Yii::t('UserModule.views_auth_recoverPassword', 'Forgot your password?'); ?></h1>
        <h5><?= Yii::t('UserModule.views_auth_recoverPassword', 'Just enter your e-mail address. We\'ll send you recovery instructions!'); ?></h5>

        <?php $form = CActiveForm::begin(['enableClientValidation' => false]); ?>

        <?= $form->field($model, 'email')->textInput(['class' => 'form-control', 'id' => 'email_txt', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'Your email')])->label(false) ?>

        <div class="form-group captcha">
            <?=\yii\captcha\Captcha::widget([
                'model' => $model,
                'attribute' => 'verifyCode',
                'captchaAction' => '/user/auth/captcha',
                'options' => ['class' => 'form-control', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'Enter security code above')]
            ]);
            ?>
            <?= $form->error($model, 'verifyCode'); ?>
        </div>

        <div class="links row">
            <div class="col-md-12">
                <?= Html::submitButton(Yii::t('UserModule.views_auth_recoverPassword', 'Reset password'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>
            </div>
            <div class="col-md-12">
                <a data-ui-loader href="<?php echo Url::home(); ?>"><?php echo Yii::t('UserModule.views_auth_recoverPassword', 'Back') ?></a>
            </div>
        </div>

        <?php CActiveForm::end(); ?>

    </div>

</div>

<script type="text/javascript">

    $(function () {
        // set cursor to email field
        $('#email_txt').focus();
    });

</script>

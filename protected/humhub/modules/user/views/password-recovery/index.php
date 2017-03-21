<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CActiveForm;

$this->pageTitle = Yii::t('UserModule.views_auth_recoverPassword', 'Password recovery');
?>
<div class="container" style="text-align: center;">
    <?= humhub\widgets\SiteLogo::widget(array('place' => 'login')); ?>
    <br>

    <div class="row">
        <div id="password-recovery-form" class="panel panel-default animated bounceIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?= Yii::t('UserModule.views_auth_recoverPassword', '<strong>Password</strong> recovery'); ?></div>
            <div class="panel-body">

                <?php $form = CActiveForm::begin(); ?>

                <p><?= Yii::t('UserModule.views_auth_recoverPassword', 'Just enter your e-mail address. We´ll send you recovery instructions!'); ?></p>

                <div class="form-group">
                    <?= $form->textField($model, 'email', array('class' => 'form-control', 'id' => 'email_txt', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'your email'))); ?>
                    <?= $form->error($model, 'email'); ?>
                </div>

                <div class="form-group">
                    <?= \yii\captcha\Captcha::widget([
                        'model' => $model,
                        'attribute' => 'verifyCode',
                        'captchaAction' => '/user/auth/captcha',
                        'options' => array('class' => 'form-control', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'enter security code above'))
                    ]);
                    ?>
                    <?= $form->error($model, 'verifyCode'); ?>
                </div>

                <hr>
                <?= Html::submitButton(Yii::t('UserModule.views_auth_recoverPassword', 'Reset password'), array('class' => 'btn btn-primary')); ?> <a class="btn btn-primary" href="<?= Url::home(); ?>"><?= Yii::t('UserModule.views_auth_recoverPassword', 'Back') ?></a>

                <?php CActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

<script>

    $(function () {
        // set cursor to email field
        $('#email_txt').focus();
    })

    // Shake panel after wrong validation
<?php if ($model->hasErrors()) { ?>
        $('#password-recovery-form').removeClass('bounceIn');
        $('#password-recovery-form').addClass('shake');
        $('#app-title').removeClass('fadeIn');
<?php } ?>
</script>

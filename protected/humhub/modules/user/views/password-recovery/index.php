<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use humhub\widgets\SiteLogo;
use yii\captcha\Captcha;

$this->pageTitle = Yii::t('UserModule.views_auth_recoverPassword', 'Password recovery');
?>
<div class="container" style="text-align: center;">
    <?=SiteLogo::widget(['place' => 'login']); ?>
    <br>

    <div class="row">
        <div id="password-recovery-form" class="panel panel-default animated bounceIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?= Yii::t('UserModule.views_auth_recoverPassword', '<strong>Password</strong> recovery'); ?></div>
            <div class="panel-body">

                <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

                <p><?= Yii::t('UserModule.views_auth_recoverPassword', 'Just enter your e-mail address. We\'ll send you recovery instructions!'); ?></p>

                <?= $form->field($model, 'email')->textInput(['class' => 'form-control', 'id' => 'email_txt', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'Your email')])->label(false) ?>

                <div class="form-group">
                    <?= $form->field($model, 'verifyCode')->widget(Captcha::class,[
                        'model' => $model,
                        'attribute' => 'verifyCode',
                        'captchaAction' => '/user/auth/captcha',
                        'options' => ['class' => 'form-control', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'Enter security code above')]
                    ])->label(false);
                    ?>
                </div>

                <hr>
                <?= Html::submitButton(Yii::t('UserModule.views_auth_recoverPassword', 'Reset password'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?> <a class="btn btn-primary" data-ui-loader href="<?php echo Url::home(); ?>"><?php echo Yii::t('UserModule.views_auth_recoverPassword', 'Back') ?></a>

                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(function () {
        // set cursor to email field
        $('#email_txt').focus();
    });

    // Shake panel after wrong validation
<?php if ($model->hasErrors()) : ?>
        $('#password-recovery-form').removeClass('bounceIn');
        $('#password-recovery-form').addClass('shake');
        $('#app-title').removeClass('fadeIn');
<?php endif; ?>
</script>

<?php

use yii\captcha\Captcha;
use \yii\helpers\Url;
use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;
use humhub\modules\user\widgets\AuthChoice;

$this->pageTitle = Yii::t('UserModule.views_auth_register', 'Register');
?>

<div class="container" style="text-align: center;">
    <?= humhub\widgets\SiteLogo::widget(['place' => 'login']); ?>
    <br>

    <div id="register-form"
         class="panel panel-default animated bounceInLeft"
         style="max-width: 300px; margin: 0 auto 20px; text-align: left;">

        <div class="panel-heading"><?= Yii::t('UserModule.views_auth_register', '<strong>Sign</strong> up') ?></div>

        <div class="panel-body">

            <p><?= Yii::t('UserModule.views_auth_register', "Don't have an account? Join the network by entering your e-mail address."); ?></p>

            <?php $form = ActiveForm::begin(['id' => 'invite-form']); ?>
            <?= $form->field($invite, 'email')->input('email', ['id' => 'register-email', 'placeholder' => $invite->getAttributeLabel('email'), 'aria-label' => $invite->getAttributeLabel('email')])->label(false); ?>
            <?php if ($invite->showCaptureInRegisterForm()) : ?>
                <div id="registration-form-captcha" style="display: none;">
                    <div><?= Yii::t('UserModule.views_auth_register', 'Please enter the letters from the image.'); ?></div>

                    <?= $form->field($invite, 'captcha')->widget(Captcha::class, [
                        'captchaAction' => 'auth/captcha',
                    ])->label(false);?>
                </div>
            <?php endif; ?>
            <hr>
            <?= CHtml::submitButton(Yii::t('UserModule.views_auth_register', 'Register'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= humhub\widgets\LanguageChooser::widget(); ?>
</div>

<script type="text/javascript">
    $(function () {
        // set cursor to login field
        $('#register-email').focus();
    });

    // Shake panel after wrong validation
<?php if ($invite->hasErrors()) { ?>
        $('#register-form').removeClass('bounceInLeft');
        $('#register-form').addClass('shake');
        $('#app-title').removeClass('fadeIn');
<?php } ?>

<?php if ($invite->showCaptureInRegisterForm()) { ?>
    $('#register-email').on('focus', function () {
        $('#registration-form-captcha').fadeIn(500);
    });
<?php } ?>

</script>



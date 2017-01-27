<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CActiveForm;

$this->pageTitle = Yii::t('UserModule.views_auth_resetPassword', 'Password reset');
?>
<div class="container" style="text-align: center;">
    <?= humhub\widgets\SiteLogo::widget(['place' => 'login']); ?>
    <br>

    <div class="row">
        <div id="password-recovery-form" class="panel panel-default animated bounceIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?= Yii::t('UserModule.views_auth_resetPassword', '<strong>Change</strong> your password'); ?></div>
            <div class="panel-body">
                <?php $form = CActiveForm::begin(['enableClientValidation'=>false]); ?>
                
                    <?= $form->field($model, 'newPassword')->passwordInput(['class' => 'form-control', 'id' => 'new_password', 'maxlength' => 255, 'value' => ''])?>

                    <?= $form->field($model, 'newPasswordConfirm')->passwordInput(['class' => 'form-control', 'maxlength' => 255, 'value' => ''])?>

                    <?= Html::submitButton(Yii::t('UserModule.views_auth_resetPassword', 'Change password'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?> 

                    <a class="btn btn-primary" data-ui-loader href="<?php echo Url::home() ?>">
                        <?= Yii::t('UserModule.views_auth_resetPassword', 'Back') ?>
                    </a>

                <?php CActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(function () {
        // set cursor to email field
        $('#new_password').focus();
    })

    // Shake panel after wrong validation
<?php if ($model->hasErrors()) { ?>
        $('#password-recovery-form').removeClass('bounceIn');
        $('#password-recovery-form').addClass('shake');
        $('#app-title').removeClass('fadeIn');
<?php } ?>
</script>

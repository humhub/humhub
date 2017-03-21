<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CActiveForm;

$this->pageTitle = Yii::t('UserModule.views_auth_resetPassword', 'Password reset');
?>
<div class="container" style="text-align: center;">
    <?= humhub\widgets\SiteLogo::widget(array('place' => 'login')); ?>
    <br>

    <div class="row">
        <div id="password-recovery-form" class="panel panel-default animated bounceIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?= Yii::t('UserModule.views_auth_resetPassword', '<strong>Change</strong> your password'); ?></div>
            <div class="panel-body">

                <?php $form = CActiveForm::begin(); ?>
                <div class="form-group">
                    <?= $form->labelEx($model, 'newPassword'); ?>
                    <?= $form->passwordField($model, 'newPassword', array('class' => 'form-control', 'maxlength' => 255, 'value' => '')); ?>
                    <?= $form->error($model, 'newPassword'); ?>
                </div>

                <div class="form-group">
                    <?= $form->labelEx($model, 'newPasswordConfirm'); ?>
                    <?= $form->passwordField($model, 'newPasswordConfirm', array('class' => 'form-control', 'maxlength' => 255, 'value' => '')); ?>
                    <?= $form->error($model, 'newPasswordConfirm'); ?>
                </div>

                <hr>
                <?= Html::submitButton(Yii::t('UserModule.views_auth_resetPassword', 'Change password'), array('class' => 'btn btn-primary')); ?> <a class="btn btn-primary" href="<?= Url::home() ?>"><?= Yii::t('UserModule.views_auth_resetPassword', 'Back') ?></a>

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

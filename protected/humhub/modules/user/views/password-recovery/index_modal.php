<?php

use humhub\compat\CActiveForm;

use yii\helpers\Url;
?>
<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo Yii::t('UserModule.views_auth_recoverPassword', '<strong>Password</strong> recovery'); ?></h4>
        </div>
        <div class="modal-body">
            <?php $form = CActiveForm::begin(['enableClientValidation' => false]); ?>

            <p><?php echo Yii::t('UserModule.views_auth_recoverPassword', 'Just enter your e-mail address. We´ll send you recovery instructions!'); ?></p>

            <div class="form-group">
                <?php echo $form->textField($model, 'email', array('class' => 'form-control', 'id' => 'email_txt', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'your email'))); ?>
                <?php echo $form->error($model, 'email'); ?>
            </div>

            <div class="form-group">
                <?php
                echo \yii\captcha\Captcha::widget([
                    'model' => $model,
                    'attribute' => 'verifyCode',
                    'captchaAction' => '/user/auth/captcha',
                    'options' => array('class' => 'form-control', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'enter security code above'))
                ]);
                ?>
                <?php echo $form->error($model, 'verifyCode'); ?>
            </div>

            <hr>
            <a href="#" class="btn btn-primary" data-action-click="ui.modal.submit" data-action-url="<?= Url::to(['/user/password-recovery']) ?>" data-ui-loader>
                <?= Yii::t('UserModule.views_auth_recoverPassword', 'Reset password') ?>
            </a>
            &nbsp;
            <a href="#" class="btn btn-default" data-action-click="ui.modal.load" data-action-url="<?= Url::to(['/user/auth/login']) ?>" data-ui-loader>
                <?= Yii::t('UserModule.views_auth_recoverPassword', 'Back') ?>
            </a>
            <?php CActiveForm::end() ?>
        </div>

    </div>
</div>


<script type="text/javascript">
<?php if ($model->hasErrors()) { ?>
        $('#password-recovery-form').removeClass('bounceIn');
        $('#password-recovery-form').addClass('shake');
        $('#app-title').removeClass('fadeIn');
<?php } ?>
</script>

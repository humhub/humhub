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
            <?php $form = CActiveForm::begin(); ?>

            <p><?php echo Yii::t('UserModule.views_auth_recoverPassword', 'Just enter your e-mail address. We´ll send you recovery instructions!'); ?></p>

            <div class="form-group">
                <?php //echo $form->labelEx($model, 'email');  ?>
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
            <?php
            echo \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('UserModule.views_auth_recoverPassword', 'Reset password'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => 'function(html){ $("#globalModal").html(html); }',
                    'url' => Url::to(['/user/password-recovery']),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary', 'id' => 'recoverPasswordBtn'
                ]
            ]);
            echo \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('UserModule.views_auth_recoverPassword', 'Back'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => 'function(html){ $("#globalModal").html(html); }',
                    'url' => Url::to(['/user/auth/login']),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary', 'id' => 'backBtn'
                ]
            ]);
            ?>
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

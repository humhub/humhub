<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo Yii::t('UserModule.views_auth_recoverPassword', '<strong>Password</strong> recovery'); ?></h4>
        </div>
        <div class="modal-body">
            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'recover-password-form',
                "enableClientValidation" => false,
                'enableAjaxValidation' => false,
            ));
            ?>

            <p><?php echo Yii::t('UserModule.views_auth_recoverPassword', 'Just enter your e-mail address. We´ll send you recovery instructions!'); ?></p>

            <div class="form-group">
                <?php //echo $form->labelEx($model, 'email');  ?>
                <?php echo $form->textField($model, 'email', array('class' => 'form-control', 'id' => 'email_txt', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'your email'))); ?>
                <?php echo $form->error($model, 'email'); ?>
            </div>

            <div class="form-group">
                <?php $this->widget('CCaptcha'); ?>                            
                <?php echo $form->textField($model, 'verifyCode', array('class' => 'form-control', 'placeholder' => Yii::t('UserModule.views_auth_recoverPassword', 'enter security code above'))); ?>
                <?php echo $form->error($model, 'verifyCode'); ?>
            </div>

            <hr>
            <?php
            echo HHtml::ajaxSubmitButton(Yii::t('UserModule.views_auth_recoverPassword', 'Reset password'), array('//user/auth/recoverPassword'), array(
                'type' => 'POST',
                'success' => 'function(html){ $("#globalModal").html(html); }',
                    ), array('class' => 'btn btn-primary', 'id' => 'recoverPasswordBtn'));
            ?>            
            <?php
            echo HHtml::ajaxLink(Yii::t('UserModule.views_auth_recoverPassword', 'Back'), array('//user/auth/login'), array(
                'type' => 'POST',
                'success' => 'function(html){ $("#globalModal").html(html); }',
                    ), array('class' => 'btn btn-primary', 'id' => 'backBtn'));
            ?>
            <?php $this->endWidget(); ?>
        </div>

    </div>
</div>    


<script type="text/javascript">
<?php if ($form->errorSummary($model) != null) { ?>
        $('#password-recovery-form').removeClass('bounceIn');
        $('#password-recovery-form').addClass('shake');
        $('#app-title').removeClass('fadeIn');
<?php } ?>
</script>

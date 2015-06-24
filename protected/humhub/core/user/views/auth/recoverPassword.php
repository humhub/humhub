<?php
$this->pageTitle = Yii::t('UserModule.views_auth_recoverPassword', '<strong>Password</strong> recovery');
?>
<div class="container" style="text-align: center;">
    <?php $this->widget('application.widgets.LogoWidget', array('place' => 'login')); ?>
    <br>

    <div class="row">
        <div id="password-recovery-form" class="panel panel-default animated bounceIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.views_auth_recoverPassword', '<strong>Password</strong> recovery'); ?></div>
            <div class="panel-body">


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
                <?php echo CHtml::submitButton(Yii::t('UserModule.views_auth_recoverPassword', 'Reset password'), array('class' => 'btn btn-primary')); ?> <a class="btn btn-primary" href="<?php echo $this->createUrl('//') ?>"><?php echo Yii::t('UserModule.views_auth_recoverPassword', 'Back') ?></a>

                <?php $this->endWidget(); ?>


            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(function() {
        // set cursor to email field
        $('#email_txt').focus();
    })

    // Shake panel after wrong validation
<?php if ($form->errorSummary($model) != null) { ?>
        $('#password-recovery-form').removeClass('bounceIn');
        $('#password-recovery-form').addClass('shake');
        $('#app-title').removeClass('fadeIn');
<?php } ?>
</script>

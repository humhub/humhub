<div class="container" style="text-align: center;">
    <h1 id="app-title" class="animated fadeIn"><?php echo Yii::app()->name; ?></h1>
    <br>

    <div class="row">
        <div id="password-recovery-form" class="panel panel-default animated bounceIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.base', '<strong>Password</strong> recovery'); ?></div>
            <div class="panel-body">


                <?php
                $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'recover-password-form',
                    "enableClientValidation" => false,
                    'enableAjaxValidation' => false,
                ));
                ?>

                <p><?php echo Yii::t('UserModule.base', 'Just enter your e-mail address. We´ll send you a new one!'); ?></p>

                <div class="form-group">
                    <?php //echo $form->labelEx($model, 'email'); ?>
                    <?php echo $form->textField($model, 'email', array('class' => 'form-control', 'id' => 'email_txt', 'placeholder' => 'your email')); ?>
                    <?php echo $form->error($model, 'email'); ?>
                </div>

                <div class="form-group">
                    <?php $this->widget('CCaptcha'); ?>                            
                    <?php echo $form->textField($model, 'verifyCode', array('class' => 'form-control', 'placeholder' => 'enter security code above')); ?>
                    <?php echo $form->error($model, 'verifyCode'); ?>
                </div>

                <hr>
                <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Get new password'), array('class' => 'btn btn-primary')); ?>

                <?php $this->endWidget(); ?>


            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(function () {
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
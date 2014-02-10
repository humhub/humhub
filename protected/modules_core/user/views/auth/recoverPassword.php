<div class="container" style="text-align: center;">
    <div class="row">
        <div class="panel panel-default" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.base', 'Password recovery'); ?></div>
            <div class="panel-body">


                <?php $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'recover-password-form',
                    "enableClientValidation" => false,
                    'enableAjaxValidation' => false,
                )); ?>

                <p><?php echo Yii::t('UserModule.base', 'Just enter your e-mail address. We´ll send you a new one!'); ?></p>

                <div class="form-group">
                    <?php //echo $form->labelEx($model, 'email'); ?>
                    <?php echo $form->textField($model, 'email', array('class' => 'form-control', 'id' => 'email_txt', 'placeholder' => 'your email')); ?>
                    <?php echo $form->error($model, 'email'); ?>
                </div>

                <hr>
                <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Get new password'), array('class' => 'btn btn-primary')); ?>

                <?php $this->endWidget(); ?>


            </div>
        </div>
        <script type="text/javascript">
            jQuery('#email_txt').focus();
        </script>


    </div>
</div>
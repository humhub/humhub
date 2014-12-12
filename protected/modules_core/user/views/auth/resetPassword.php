<?php
$this->pageTitle = Yii::t('UserModule.views_auth_resetPassword', '<strong>Password</strong> reset');
?>
<div class="container" style="text-align: center;">
    <h1 id="app-title" class="animated fadeIn"><?php echo Yii::app()->name; ?></h1>
    <br>

    <div class="row">
        <div id="password-recovery-form" class="panel panel-default animated bounceIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.views_auth_resetPassword', '<strong>Change</strong> your password'); ?></div>
            <div class="panel-body">


                <?php
                $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'reset-password-form',
                    "enableClientValidation" => false,
                    'enableAjaxValidation' => false,
                ));
                ?>
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'newPassword'); ?>
                    <?php echo $form->passwordField($model, 'newPassword', array('class' => 'form-control', 'maxlength' => 255, 'value'=>'')); ?>
                    <?php echo $form->error($model, 'newPassword'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model, 'newPasswordConfirm'); ?>
                    <?php echo $form->passwordField($model, 'newPasswordConfirm', array('class' => 'form-control', 'maxlength' => 255, 'value'=>'')); ?>
                    <?php echo $form->error($model, 'newPasswordConfirm'); ?>
                </div>


                <hr>
                <?php echo CHtml::submitButton(Yii::t('UserModule.views_auth_resetPassword', 'Change password'), array('class' => 'btn btn-primary')); ?> <a class="btn btn-primary" href="<?php echo $this->createUrl('//') ?>"><?php echo Yii::t('UserModule.views_auth_resetPassword', 'Back') ?></a>

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

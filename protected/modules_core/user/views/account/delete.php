<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_delete', '<strong>Delete</strong> account'); ?>
</div>

<div class="panel-body">
    <?php if ($isSpaceOwner) { ?>

        <?php echo Yii::t('UserModule.views_account_delete', 'Sorry, as an owner of a workspace you are not able to delete your account!<br />Please assign another owner or delete them.'); ?>

    <?php } else { ?>

        <?php echo Yii::t('UserModule.views_account_delete', 'Are you sure, that you want to delete your account?<br />All your published content will be removed! '); ?>

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'user-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <p class="help-block">Fields with <span class="required">*</span> are required.</p><br>

        <?php echo $form->errorSummary($model); ?>
        <div class="form-group">
            <?php echo $form->passwordField($model, 'currentPassword', array('class' => 'form-control', 'placeholder' => Yii::t('UserModule.views_account_delete', 'Enter your password to continue'), 'maxlength' => 45)); ?>
        </div>
        <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_delete', 'Delete account'), array('class' => 'btn btn-danger')); ?>


        <?php $this->endWidget(); ?>

    <?php } ?>
</div>



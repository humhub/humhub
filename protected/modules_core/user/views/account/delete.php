<div class="panel-heading">
    <?php echo Yii::t('UserModule.base', 'Delete Account'); ?>
</div>

<div class="panel-body">
    <?php if ($isSpaceOwner) { ?>

        <?php echo Yii::t('UserModule.base', 'Sorry, as an owner of a workspace you are not able to delete your account!<br />Please assign another owner or delete them.'); ?>

    <?php } else { ?>

        <?php echo Yii::t('UserModule.base', 'Are you sure, that you want to delete your account?<br />All your published content will be removed! '); ?>

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'user-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <p class="help-block">Fields with <span class="required">*</span> are required.</p><br>

        <?php echo $form->errorSummary($model); ?>

        <?php echo $form->passwordField($model, 'currentPassword', array('class' => 'span8', 'maxlength' => 45)); ?>

        <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Delete account'), array('class' => 'btn btn-danger')); ?>


        <?php $this->endWidget(); ?>

    <?php } ?>
</div>



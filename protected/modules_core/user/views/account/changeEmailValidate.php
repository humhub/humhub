<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_changeEmailValidate', '<strong>Change</strong> E-mail'); ?>
</div>
<div class="panel-body">
    <?php echo Yii::t('UserModule.views_account_changeEmailValidate', 'Your e-mail address has been successfully changed to {email}.', array('{email}' => CHtml::encode($newEmail))); ?>
</div>


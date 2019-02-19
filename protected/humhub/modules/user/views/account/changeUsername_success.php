<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <?php echo Yii::t('UserModule.views_account_changeUsername_success', 'Your username has been successfully changed. <br> We´ve just sent an e-mail to you with new information.'); ?>
<?php $this->endContent(); ?>

<!-- show flash message after saving -->
<?php echo $this->saved(); ?>


<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <?php echo Yii::t('UserModule.account', 'Your username has been successfully changed. <br> We´ve just sent an e-mail to you with new information.'); ?>
<?php $this->endContent(); ?>

<!-- show flash message after saving -->
<?php echo $this->saved(); ?>


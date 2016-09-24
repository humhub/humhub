<?php

use \humhub\compat\CHtml;
?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <?php echo Yii::t('UserModule.views_account_changeEmailValidate', 'Your e-mail address has been successfully changed to {email}.', array('{email}' => CHtml::encode($newEmail))); ?>
<?php $this->endContent(); ?>


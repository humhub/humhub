<?php

use \humhub\compat\CHtml;
?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <?php echo Yii::t('UserModule.account', 'Your e-mail address has been successfully changed to {email}.', ['{email}' => CHtml::encode($newEmail)]); ?>
<?php $this->endContent(); ?>


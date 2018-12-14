<?php

use \humhub\compat\CHtml;
?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <?php echo Yii::t('UserModule.views_account_changeEmailValidate', 'Your username has been successfully changed to {username}.', ['{username}' => CHtml::encode($newUsername)]); ?>
<?php $this->endContent(); ?>


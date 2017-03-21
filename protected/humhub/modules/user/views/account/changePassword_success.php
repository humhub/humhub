<?php
$this->beginContent('@user/views/account/_userProfileLayout.php');
    echo Yii::t('UserModule.views_account_changePassword_success', 'Your password has been successfully changed!');
$this->endContent();
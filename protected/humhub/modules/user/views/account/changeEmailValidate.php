<?php

use \humhub\compat\CHtml;

$this->beginContent('@user/views/account/_userProfileLayout.php')
    echo Yii::t('UserModule.views_account_changeEmailValidate', 'Your e-mail address has been successfully changed to {email}.', array('{email}' => CHtml::encode($newEmail)));
$this->endContent();
<?php
$this->beginContent('@user/views/account/_userProfileLayout.php');
    echo Yii::t('UserModule.views_account_changeEmail_success', 'We´ve just sent an confirmation e-mail to your new address. <br /> Please follow the instructions inside.');
$this->endContent();
<?php

use yii\helpers\Html;
?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <?= Yii::t('UserModule.views_account_changeUsername', 'Your username has been successfully changed to {username}.', ['{username}' => Html::encode($newUsername)]); ?>
<?php $this->endContent();

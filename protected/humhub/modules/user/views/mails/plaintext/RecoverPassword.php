<?php

use humhub\modules\user\models\User;

/* @var $user User */
/* @var $linkPasswordReset string */

?>
<?= strip_tags(Yii::t('UserModule.auth', '<strong>Password</strong> recovery')) ?>


<?= Yii::t('UserModule.auth', 'Hello {displayName}', ['{displayName}' => $user->displayName]) ?>


<?= Yii::t('UserModule.auth', 'Please use the following link within the next day to reset your password.') ?>

<?= Yii::t('UserModule.auth', "If you don't use this link within 24 hours, it will expire.") ?>


<?= Yii::t('UserModule.auth', 'Reset Password') ?>: <?= urldecode($linkPasswordReset) ?>

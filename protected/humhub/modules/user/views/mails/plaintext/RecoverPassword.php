<?php

use yii\helpers\Html;

?>
<?php echo strip_tags(Yii::t('UserModule.auth', '<strong>Password</strong> recovery')); ?>


<?php echo strip_tags(Yii::t('UserModule.auth', 'Hello {displayName}', ['{displayName}' => Html::encode($user->displayName)])); ?>


<?php echo strip_tags(Yii::t('UserModule.auth', 'Please use the following link within the next day to reset your password.')); ?>

<?php echo strip_tags(Yii::t('UserModule.auth', "If you don't use this link within 24 hours, it will expire.")); ?>


<?php echo strip_tags(Yii::t('UserModule.auth', 'Reset Password')); ?>: <?php echo urldecode($linkPasswordReset); ?>

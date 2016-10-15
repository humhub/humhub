<?php

use yii\helpers\Html;

?>
<?php echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', '<strong>Password</strong> recovery')); ?>


<?php echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', 'Hello {displayName}', array('{displayName}' => Html::encode($user->displayName)))); ?>


<?php echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', 'Please use the following link within the next day to reset your password.')); ?>

<?php echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', "If you don't use this link within 24 hours, it will expire.")); ?>


<?php echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', 'Reset Password')); ?>: <?php echo urldecode($linkPasswordReset); ?>

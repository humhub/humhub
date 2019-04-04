<?php

use yii\helpers\Html;
?>
<?php echo strip_tags(Yii::t('UserModule.views_mails_ChangeUsername', 'Your username has been changed')); ?>


<?php echo strip_tags(Yii::t('UserModule.views_mails_ChangeUsername', 'Hello')); ?> <?php echo Html::encode($user->displayName); ?>,

<?php echo strip_tags(str_replace("<br>", "\n", Yii::t('UserModule.views_mails_ChangeUsername', 'You have successfully changed your username.<br>Your new username is {newUsername}.', ['{newUsername}' => Html::encode($newUsername)]))); ?>
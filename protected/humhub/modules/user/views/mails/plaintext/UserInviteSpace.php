<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\models\Setting;
?>
<?php echo strip_tags(Yii::t('UserModule.views_mails_UserInviteSpace', 'You got a <strong>space</strong> invite')); ?>


<?php echo Html::encode($originator->displayName); ?> <?php echo strip_tags(Yii::t('UserModule.views_mails_UserInviteSpace', 'invited you to the space:')); ?> <?php echo Html::encode($space->name); ?> at <?php echo Html::encode(Yii::$app->name); ?>

<?php echo strip_tags(str_replace(["\n","<br>"], [" ","\n"], Yii::t('UserModule.views_mails_UserInviteSpace', '<br>A social network to increase your communication and teamwork.<br>Register now
to join this space.'))); ?>


<?php echo strip_tags(Yii::t('UserModule.views_mails_UserInviteSpace', 'Sign up now')); ?>: <?php echo urldecode(Url::to(['/user/auth/create-account', 'token' => $token], true)); ?>

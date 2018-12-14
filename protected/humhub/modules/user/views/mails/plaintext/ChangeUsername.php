<?php
	use yii\helpers\Html;
?>
<?php echo strip_tags(Yii::t('UserModule.views_mails_ChangeUsername', '<strong>Confirm</strong></strong> your new username')); ?>

<?php echo strip_tags(Yii::t('UserModule.views_mails_ChangeUsername', 'Hello')); ?> <?php echo Html::encode($user->displayName); ?>,

<?php echo strip_tags(str_replace("<br>", "\n", Yii::t('UserModule.views_mails_ChangeUsername',
													   'You have requested to change your username.<br>Your new username is {newUsername}.<br><br>To confirm your new username please click on the button below.',
													   ['{newUsername}' => Html::encode($newUsername)]))); ?>


<?php echo strip_tags(Yii::t('UserModule.views_mails_ChangeUsername', 'Confirm')); ?>: <?php echo urldecode($approveUrl); ?>

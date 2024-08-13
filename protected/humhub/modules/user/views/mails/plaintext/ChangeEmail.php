<?php

use humhub\modules\user\models\User;

/* @var $user User */
/* @var $newEmail string */
/* @var $approveUrl string */

$text = Yii::t(
    'UserModule.account',
    'You have requested to change your e-mail address.<br>Your new e-mail address is {newemail}.<br><br>To confirm your new e-mail address please click on the button below.',
    ['{newemail}' => $newEmail]);

$text = str_replace("<br>", "\n", $text);
?>
<?= strip_tags(Yii::t('UserModule.account', '<strong>Confirm</strong></strong> your new email address')) ?>


<?= Yii::t('UserModule.account', 'Hello') ?> <?= $user->displayName ?>,

<?= $text ?>


<?= Yii::t('UserModule.account', 'Confirm') ?>: <?= urldecode($approveUrl) ?>

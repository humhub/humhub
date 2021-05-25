<?php

use humhub\modules\user\models\User;

/* @var $user User */
/* @var $newUsername string */

$text = Yii::t('UserModule.account',
    'You have successfully changed your username.<br>Your new username is {newUsername}.',
    ['{newUsername}' =>$newUsername]);

$text = str_replace("<br>", "\n", $text);
?>
<?= Yii::t('UserModule.account', 'Your username has been changed') ?>


<?= Yii::t('UserModule.account', 'Hello') ?> <?= $user->displayName ?>,

<?= $text ?>

<?php
/* @var string $passwordRecoveryUrl */
?>
<?= mb_strtoupper(Yii::t('UserModule.base', 'Welcome to %appName%', ['%appName%' => Yii::$app->name])) ?>


<?= Yii::t('UserModule.base',
    'You just tried registering at %appName% with this email address, but you already have an account connected to this email address.',
    ['%appName%' => Yii::$app->name]) ?>

<?= Yii::t('UserModule.base', 'Did you forget the password?') ?>

<?= Yii::t('UserModule.base', 'If it wasn\'t you, just discard this message.') ?>


<?= Yii::t('UserModule.base', 'Password recovery') ?>: <?= $passwordRecoveryUrl ?>

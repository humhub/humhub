<?php
/* @var string $passwordRecoveryUrl */
?>
<?= mb_strtoupper(Yii::t('UserModule.base', 'Welcome to %appName%', ['%appName%' => Yii::$app->name])) ?>


<?= Yii::t(
    'UserModule.base',
    'You tried registering an account with %appName%, but already have an account associated with this email address.',
    ['%appName%' => Yii::$app->name]
) ?>

<?= Yii::t('UserModule.base', 'Did you forget your password?') ?>

<?= Yii::t('UserModule.base', 'If this wasn\'t you, you can disregard this message.') ?>


<?= Yii::t('UserModule.base', 'Password recovery') ?>: <?= $passwordRecoveryUrl ?>

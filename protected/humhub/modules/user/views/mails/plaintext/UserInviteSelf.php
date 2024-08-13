<?php


use yii\helpers\Html;

/* @var $registrationUrl string */

?>
<?= mb_strtoupper(Yii::t('UserModule.base', 'Welcome to %appName%', ['%appName%' => Yii::$app->name])) ?>


<?= Yii::t('UserModule.base',
    'Welcome to %appName%. Please click on the button below to proceed with your registration.',
    ['%appName%' => Yii::$app->name]); ?>


<?= Yii::t('UserModule.base', 'Sign up') ?>: <?= $registrationUrl ?>

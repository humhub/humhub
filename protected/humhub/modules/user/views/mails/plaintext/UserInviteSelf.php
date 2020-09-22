<?php


use yii\helpers\Html;

?>
<?php echo mb_strtoupper(Yii::t('UserModule.base', 'Welcome to %appName%', ['%appName%' => Html::encode(Yii::$app->name)])); ?>


<?php echo strip_tags(Yii::t('UserModule.base', 'Welcome to %appName%. Please click on the button below to proceed with your registration.', ['%appName%' => Html::encode(Yii::$app->name)])); ?>


<?php echo strip_tags(Yii::t('UserModule.base', 'Sign up')); ?>: <?php echo $registrationUrl; ?>

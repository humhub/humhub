<?php echo Yii::t('UserModule.mail', '{username} invited you to {name}.', ['username' => $originator->displayName, 'name' => Yii::$app->name]); ?>


<?php echo Yii::t('UserModule.mail', 'Click here to create an account:'); ?>

<?php echo $registrationUrl; ?>

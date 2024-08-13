<?php echo Yii::t('UserModule.invite', '{username} invited you to {name}.', ['username' => $originator->displayName, 'name' => Yii::$app->name]); ?>


<?php echo Yii::t('UserModule.invite', 'Click here to create an account:'); ?>

<?php echo $registrationUrl; ?>

<?= Yii::t('UserModule.mail', '{username} invited you to {name}.', ['username' => $originator->displayName, 'name' => Yii::$app->name]);

echo Yii::t('UserModule.mail', 'Click here to create an account:');

echo $registrationUrl;
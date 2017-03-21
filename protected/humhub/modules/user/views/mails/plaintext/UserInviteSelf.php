<?php

use yii\helpers\Html;

echo mb_strtoupper(Yii::t('UserModule.views_mails_UserInviteSelf', 'Welcome to %appName%', array('%appName%' => Html::encode(Yii::$app->name))));

echo strip_tags(Yii::t('UserModule.views_mails_UserInviteSelf', 'Welcome to %appName%. Please click on the button below to proceed with your registration.', array('%appName%' => Html::encode(Yii::$app->name))));

echo strip_tags(Yii::t('UserModule.views_mails_UserInviteSelf', 'Sign up')); ?>: <?= echo $registrationUrl;
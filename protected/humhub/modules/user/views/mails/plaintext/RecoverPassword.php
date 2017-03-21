<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', '<strong>Password</strong> recovery'));

echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', 'Hello {displayName}', array('{displayName}' => Html::encode($user->displayName))));

echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', 'Please use the following link within the next day to reset your password.'));

echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', "If you don't use this link within 24 hours, it will expire."));

echo strip_tags(Yii::t('UserModule.views_mails_RecoverPassword', 'Reset Password')); ?>: <?= echo urldecode($linkPasswordReset);

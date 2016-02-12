<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('UserModule.views_notifications_follow', '{userName} is now following you.', array(
    '{userName}' => Html::encode($originator->displayName),
)));
?>

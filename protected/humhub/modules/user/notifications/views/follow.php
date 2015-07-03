<?php

use yii\helpers\Html;

echo Yii::t('UserModule.views_notifications_follow', '{userName} is now following you.', array(
    '{userName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
));
?>

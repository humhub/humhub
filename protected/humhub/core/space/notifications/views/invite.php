<?php

use yii\helpers\Html;

echo Yii::t('SpaceModule.views_notifications_invite', '{userName} invited you to the space {spaceName}', array(
    '{userName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{spaceName}' => '<strong>' . Html::encode($source->name) . '</strong>'
));
?>

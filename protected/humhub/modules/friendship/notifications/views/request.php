<?php

use yii\helpers\Html;

echo Yii::t('FriendshipModule.notifications', '{userName} sent you an friend request.', array(
    '{userName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
));
?>

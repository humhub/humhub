<?php

use yii\helpers\Html;

echo Yii::t('FriendshipModule.notifications', '{userName} accepted your friend request.', array(
    '{userName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
));
?>

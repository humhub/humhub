<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('SpaceModule.views_notifications_approvalRequestAccepted', '{userName} approved your membership for the space {spaceName}', array(
    '{userName}' => Html::encode($originator->displayName),
    '{spaceName}' => Html::encode($source->name)
)));
?>
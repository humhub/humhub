<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('SpaceModule.views_notifications_approvalRequestDeclined', '{userName} declined your membership request for the space {spaceName}', array(
    '{userName}' => Html::encode($originator->displayName),
    '{spaceName}' => Html::encode($source->name)
)));
?>

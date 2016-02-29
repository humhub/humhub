<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('LikeModule.views_activities_Like', '{userDisplayName} likes {contentTitle}', array(
    '{userDisplayName}' => Html::encode($originator->displayName),
    '{contentTitle}' => $preview,
)));
?>

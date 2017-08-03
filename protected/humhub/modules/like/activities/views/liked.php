<?php

use yii\helpers\Html;

echo Yii::t('LikeModule.views_activities_Like', '{userDisplayName} likes {contentTitle}', [
    '{userDisplayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => $preview,
]);

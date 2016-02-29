<?php

use yii\helpers\Html;
use humhub\libs\Helpers;

echo strip_tags(Yii::t('ActivityModule.views_activities_ActivitySpaceCreated', "%displayName% created the new space %spaceName%", array(
    '%displayName%' => Html::encode($originator->displayName),
    '%spaceName%' => '"' . Html::encode(Helpers::truncateText($source->name, 25)) . '"'
)));
?>

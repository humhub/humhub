<?php

use yii\helpers\Html;
use humhub\libs\Helpers;

echo Yii::t('ActivityModule.views_activities_ActivitySpaceMemberAdded', "%displayName% joined the space %spaceName%", array(
    '%displayName%' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '%spaceName%' => '<strong>' . Html::encode(Helpers::truncateText($source->name, 40)) . '</strong>'
));
?>
<br/>

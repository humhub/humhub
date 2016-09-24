<?php

use yii\helpers\Html;
use humhub\libs\Helpers;
use humhub\modules\content\components\ContentContainerController;

if (!Yii::$app->controller instanceof ContentContainerController) {
    echo Yii::t('ActivityModule.views_activities_ActivitySpaceMemberAdded', "%displayName% joined the space %spaceName%", array(
        '%displayName%' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
        '%spaceName%' => '<strong>' . Html::encode(Helpers::truncateText($source->name, 40)) . '</strong>'
    ));
} else {
    echo Yii::t('ActivityModule.views_activities_ActivitySpaceMemberAdded', "%displayName% joined this space.", array(
        '%displayName%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
    ));
}

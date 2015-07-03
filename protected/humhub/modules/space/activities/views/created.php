<?php

use yii\helpers\Html;
use humhub\libs\Helpers;
use humhub\modules\content\components\ContentContainerController;

if (!Yii::$app->controller instanceof ContentContainerController) {
    echo Yii::t('ActivityModule.views_activities_ActivitySpaceCreated', "%displayName% created the new space %spaceName%", array(
        '%displayName%' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
        '%spaceName%' => '<strong>' . Html::encode(Helpers::truncateText($source->name, 25)) . '</strong>'
    ));
} else {
    echo Yii::t('ActivityModule.views_activities_ActivitySpaceCreated', "%displayName% created this space.", array(
        '%displayName%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
    ));
}
?>

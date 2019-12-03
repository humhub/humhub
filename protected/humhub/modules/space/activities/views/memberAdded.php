<?php

use humhub\libs\Helpers;
use humhub\modules\content\components\ContentContainerController;
use yii\helpers\Html;

if (!Yii::$app->controller instanceof ContentContainerController) {
    echo Yii::t('ActivityModule.base', '{displayName} joined the space {spaceName}', [
        '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
        '{spaceName}' => '<strong>' . Html::encode(Helpers::truncateText($source->name, 40)) . '</strong>'
    ]);
} else {
    echo Yii::t('ActivityModule.base', '{displayName} joined this space.', [
        '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
    ]);
}

<?php

use yii\helpers\Html;

echo Yii::t('ContentModule.activities_views_created', '{displayName} created a new {contentTitle}.', [
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => $this->context->getContentInfo($source)
]);
?>
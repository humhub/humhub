<?php

use yii\helpers\Html;
use humhub\libs\Helpers;

echo strip_tags(Yii::t('ActivityModule.base', "{displayName} created the new space {spaceName}", [
    '{displayName}' => Html::encode($originator->displayName),
    '{spaceName}' => '"' . Html::encode(Helpers::truncateText($source->name, 25)) . '"'
]));
?>

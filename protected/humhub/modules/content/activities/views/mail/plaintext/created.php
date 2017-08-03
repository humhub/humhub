<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('ContentModule.activities_views_created', '{displayName} created a new {contentTitle}.', [
    '{displayName}' => Html::encode($originator->displayName),
    '{contentTitle}' => html_entity_decode(Html::encode($source->getContentName()))
]));
?>

"<?= strip_tags(\humhub\widgets\RichText::widget(['text' => $source->getContentDescription(), 'minimal' => true])); ?>"

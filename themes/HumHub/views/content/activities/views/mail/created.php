<?php

use yii\helpers\Html;

echo Yii::t('ContentModule.activities_views_created', '{displayName} created a new {contentTitle}.', array(
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => '<strong>' . Html::encode($source->getContentName()) . '</strong>'
));
?>
<br />
<em>"<?php echo \humhub\widgets\RichText::widget(['text' => $source->getContentDescription(), 'minimal' => true]); ?>"</em>
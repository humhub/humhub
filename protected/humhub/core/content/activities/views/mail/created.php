<?php

use yii\helpers\Html;

echo Yii::t('PostModule.views_activities_PostCreated', '{displayName} created a new {contentTitle}.', array(
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => '<strong>' . Html::encode($source->getContentTitle()) . '</strong>'
));
?>
<br />
<em>"<?php echo \humhub\widgets\RichText::widget(['text' => $source->getContentPreview(), 'minimal' => true]); ?>"</em>

<?php

use yii\helpers\Html;

echo Yii::t('PostModule.views_activities_PostCreated', '{displayName} created a new {contentTitle}.', array(
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => $this->context->getContentInfo($source)
));
?>

<?php //echo $source->getContentPreview(); ?>

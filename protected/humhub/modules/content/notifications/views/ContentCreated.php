<?php

use yii\helpers\Html;

echo Yii::t('ContentModule.notifications_views_ContentCreated', '{userName} created a new {contentTitle}.', array(
    '{userName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => $this->context->getContentInfo($source)
));
?>

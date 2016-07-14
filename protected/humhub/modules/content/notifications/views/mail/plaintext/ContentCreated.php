<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('ContentModule.notifications_views_ContentCreated', '{userName} created a new {contentTitle}.', array(
    '{userName}' => Html::encode($originator->displayName),
    '{contentTitle}' => $this->context->getContentInfo($source)
)));
?>

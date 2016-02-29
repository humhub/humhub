<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('LikeModule.views_notifications_newLike', "%displayName% likes %contentTitle%.", array(
    '%displayName%' => Html::encode($originator->displayName),
    '%contentTitle%' => $this->context->getContentInfo($source->getSource())
)));
?>

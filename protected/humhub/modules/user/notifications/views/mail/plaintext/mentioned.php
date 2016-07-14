<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('UserModule.views_notifications_Mentioned', '{userName} mentioned you in {contentTitle}.', array(
    '{userName}' => Html::encode($originator->displayName),
    '{contentTitle}' => $this->context->getContentInfo($source)
)));
?>

<?php

use yii\helpers\Html;

echo Yii::t('UserModule.views_notifications_Mentioned', '{userName} mentioned you in {contentTitle}.', array(
    '{userName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => $this->context->getContentInfo($source)
));
?>

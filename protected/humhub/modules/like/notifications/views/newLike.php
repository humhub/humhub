<?php

use yii\helpers\Html;

echo Yii::t('LikeModule.views_notifications_newLike', "%displayName% likes %contentTitle%.", array(
    '%displayName%' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '%contentTitle%' => $this->context->getContentInfo($source->getSource())
));
?>

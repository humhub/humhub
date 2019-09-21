<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('LikeModule.activities', '{userDisplayName} likes {contentTitle}', [
    '{userDisplayName}' => Html::encode($originator->displayName),
    '{contentTitle}' => $preview,
]));
?>

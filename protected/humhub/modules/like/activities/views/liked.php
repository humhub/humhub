<?php

use humhub\widgets\bootstrap\Html;

echo Yii::t('LikeModule.activities', '{userDisplayName} likes {contentTitle}', [
    '{userDisplayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => $preview,
]);

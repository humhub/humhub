<?php

use humhub\modules\user\models\User;

/* @var $originator User */
/* @var $preview string */

echo Yii::t('LikeModule.activities', '{userDisplayName} likes {contentTitle}', [
    '{userDisplayName}' => $originator->displayName,
    '{contentTitle}' => $preview,
]);

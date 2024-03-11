<?php

use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\libs\Helpers;

/* @var $originator User */
/* @var $source Space */

echo Yii::t('ActivityModule.base', "{displayName} joined the space {spaceName}", [
    '{displayName}' => $originator->displayName,
    '{spaceName}' => '"' . Helpers::truncateText($source->name, 40) . '"'
]);

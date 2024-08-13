<?php

use humhub\libs\Helpers;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/* @var $originator User */
/* @var $source Space */

echo Yii::t('ActivityModule.base', "{displayName} created the new space {spaceName}", [
    '{displayName}' => $originator->displayName,
    '{spaceName}' => '"' . Helpers::truncateText($source->name, 25) . '"'
]);
?>

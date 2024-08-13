<?php

use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;

/* @var $originator User */
/* @var $source Follow */

echo Yii::t('ActivityModule.base', '{user1} now follows {user2}.', [
    '{user1}' => $originator->displayName,
    '{user2}' => $source->getTarget()->displayName,
]);

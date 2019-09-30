<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('ActivityModule.base', '{user1} now follows {user2}.', [
    '{user1}' => Html::encode($originator->displayName),
    '{user2}' => Html::encode($source->getTarget()->displayName),
]));

?>

<?php

use yii\helpers\Html;

echo Yii::t('ActivityModule.base', '{user1} now follows {user2}.', [
    '{user1}' => '<strong>' .Html::encode($originator->displayName). '</strong>',
    '{user2}' => '<strong>' . Html::encode($source->getTarget()->displayName) . '</strong>',
]);

?>

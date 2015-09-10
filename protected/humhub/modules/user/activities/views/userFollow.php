<?php

use yii\helpers\Html;

echo Yii::t('ActivityModule.views_activities_ActivityUserFollowsUser', '{user1} now follows {user2}.', array(
    '{user1}' => '<strong>' .Html::encode($originator->displayName). '</strong>',
    '{user2}' => '<strong>' . Html::encode($source->getTarget()->displayName) . '</strong>',
));

?>

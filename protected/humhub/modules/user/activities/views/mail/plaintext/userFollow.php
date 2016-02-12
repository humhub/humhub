<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('ActivityModule.views_activities_ActivityUserFollowsUser', '{user1} now follows {user2}.', array(
    '{user1}' => Html::encode($originator->displayName),
    '{user2}' => Html::encode($source->getTarget()->displayName),
)));

?>

<?php

use yii\helpers\Html;

if ($space->isFollowedByUser()) {
    print Html::a(Yii::t('SpaceModule.widgets_views_followButton', "Unfollow"), $space->createUrl('/space/space/unfollow'), array('data-method' => 'POST', 'class' => 'btn btn-primary'));
} else {
    print Html::a(Yii::t('SpaceModule.widgets_views_followButton', "Follow"), $space->createUrl('/space/space/follow'), array('data-method' => 'POST', 'class' => 'btn btn-primary'));
}

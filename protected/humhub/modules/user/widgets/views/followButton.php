<?php

use yii\helpers\Html;

if ($user->isFollowedByUser()) {
    print Html::a(Yii::t("UserModule.widgets_views_followButton", "Unfollow"), $user->createUrl('/user/profile/unfollow'), array('class' => 'btn btn-primary', 'data-method'=>'POST'));
} else {
    print Html::a(Yii::t("UserModule.widgets_views_followButton", "Follow"), $user->createUrl('/user/profile/follow'), array('class' => 'btn btn-info', 'data-method'=>'POST'));
}
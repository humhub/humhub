<?php

if ($user->isFollowedByUser()) {
    print HHtml::postLink(Yii::t("UserModule.widgets_views_followButton", "Unfollow"), $user->createUrl('//user/profile/unfollow'), array('class' => 'btn btn-primary'));
} else {
    print HHtml::postLink(Yii::t("UserModule.widgets_views_followButton", "Follow"), $user->createUrl('//user/profile/follow'), array('class' => 'btn btn-success'));
}
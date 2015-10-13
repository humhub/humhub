<?php

if ($space->isFollowedByUser()) {
    print HHtml::postLink(Yii::t('SpaceModule.widgets_views_followButton', "Unfollow"), $space->createUrl('//space/space/unfollow'), array('class' => 'btn btn-primary'));
} else {
    print HHtml::postLink(Yii::t('SpaceModule.widgets_views_followButton', "Follow"), $space->createUrl('//space/space/follow'), array('class' => 'btn btn-primary'));
}

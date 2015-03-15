<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity)); ?>

<?php echo Yii::t('ActivityModule.views_activities_ActivityUserFollowsUser', '{user1} now follows {user2}.', array(
    '{user1}' => '<a href="' . $user->getUrl() . '">' . CHtml::encode($user->displayName) . '</a>',
    '{user2}' => '<a href="' . $target->getUrl() . '">' . CHtml::encode($target->displayName) . '</a>',
));
?>
<br/>

<?php $this->endContent(); ?>


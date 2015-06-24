<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity)); ?>

<?php echo Yii::t('ActivityModule.views_activities_ActivityUserFollowsUser', '{user1} now follows {user2}.', array(
    '{user1}' => '<strong>' . CHtml::encode($user->displayName) . '</strong>',
    '{user2}' => '<strong>' . CHtml::encode($target->displayName) . '</strong>',
));
?>
<br/>

<?php $this->endContent(); ?>


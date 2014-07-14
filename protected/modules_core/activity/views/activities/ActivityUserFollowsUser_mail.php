<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity)); ?>

<?php echo Yii::t('ActivityModule.base', '{user1} now follows {user2}.', array(
    '{user1}' => '<strong>' . $user->displayName . '</strong>',
    '{user2}' => '<strong>' . $target->displayName . '</strong>',
));
?>
<br/>

<?php $this->endContent(); ?>


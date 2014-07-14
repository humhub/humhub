<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity, 'showSpace' => true)); ?>

<?php
echo Yii::t('PostModule.base', '%displayName% created a new post.', array(
    '%displayName%' => '<strong>' . $user->displayName . '</strong>'
));
?>
<br />

<em>"<?php echo ActivityModule::formatOutput($target->message); ?>"</em>

<?php $this->endContent(); ?>    
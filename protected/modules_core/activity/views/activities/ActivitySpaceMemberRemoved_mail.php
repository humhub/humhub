<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity, 'showSpace' => false)); ?>

<?php echo Yii::t('ActivityModule.base', "%displayName% left the space %spaceName%", array(
    '%displayName%' => '<strong>' . $user->displayName . '</strong>',
    '%spaceName%' => '<strong>' . Helpers::truncateText($workspace->name, 40) . '</strong>'
)); ?>
<br/>

<?php $this->endContent(); ?>


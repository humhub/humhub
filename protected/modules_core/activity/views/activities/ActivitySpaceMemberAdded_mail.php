<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity, 'showSpace' => false)); ?>

<?php echo Yii::t('ActivityModule.views_activities_ActivitySpaceMemberAdded', "%displayName% joined the space %spaceName%", array(
    '%displayName%' => '<a href="' . $user->getUrl() . '">'.CHtml::encode($user->displayName).'</a>',
    '%spaceName%' => '<a href="' . $workspace->getUrl() . '">'. CHtml::encode(Helpers::truncateText($workspace->name, 40)).'</a>'
)); ?>
<br/>

<?php $this->endContent(); ?>


<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>

<?php echo Yii::t('LikeModule.views_activities_Like', '{userDisplayName} likes {contentTitle}', array(
    '{userDisplayName}' => '<a href="' . $user->getUrl() . '">' . CHtml::encode($user->displayName) . '</a>',
    '{contentTitle}' => ActivityModule::formatOutput($target->getContentTitle()),
)); ?>

<?php $this->endContent(); ?>

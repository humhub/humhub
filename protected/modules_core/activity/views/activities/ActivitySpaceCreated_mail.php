<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity, 'showSpace' => true)); ?>

<?php echo Yii::t('ActivityModule.base', "<strong>%displayName%</strong> created the new space <strong>%spaceName%</strong>", array(
    '%displayName%' => $user->displayName,
    '%spaceName%' => Helpers::truncateText($workspace->name, 25)
)); ?>
<br/>

<?php $this->endContent(); ?>

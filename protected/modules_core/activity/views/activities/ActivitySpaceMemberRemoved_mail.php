<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity, 'showSpace' => false)); ?>

<?php echo Yii::t('ActivityModule.base', "<strong>%displayName%</strong> left the space <strong>%spaceName%</strong>", array(
    '%displayName%' => $user->displayName,
    '%spaceName%' => Helpers::truncateText($workspace->name, 40)
)); ?>
<br/>

<?php $this->endContent(); ?>


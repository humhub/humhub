<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    

<?php echo Yii::t('LikeModule.base', '<strong>{userDisplayName}</strong> likes {contentTitle}', array(
  '{userDisplayName}' => $user->displayName,
  '{contentTitle}' => $target->getContentTitle(),
)); ?>

<?php $this->endContent(); ?>

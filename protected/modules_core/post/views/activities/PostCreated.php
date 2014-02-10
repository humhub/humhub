<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    

<strong><?php echo $user->displayName; ?></strong>
<?php echo Yii::t('PostModule.base', 'created a new post'); ?>.
<?php $this->endContent(); ?>

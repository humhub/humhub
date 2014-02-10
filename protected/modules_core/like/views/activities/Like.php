<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    

<strong><?php echo $user->displayName; ?></strong>
<?php echo Yii::t('LikeModule.base', 'likes'); ?> <?php echo $target->getContentTitle(); ?>

<?php $this->endContent(); ?>

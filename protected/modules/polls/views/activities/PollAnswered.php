<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    

<strong><?php echo $user->displayName; ?></strong>
<?php echo Yii::t('PollsModule.base', 'voted in question'); ?> "<i><?php echo Helpers::truncateText($target->question, 25); ?></i>".

<?php if ($workspace != null && Wall::$currentType != Wall::TYPE_SPACE): ?>
    <strong>
        <?php echo Helpers::truncateText($workspace->name, 40); ?> -
    </strong>
<?php endif; ?>

<?php $this->endContent(); ?>

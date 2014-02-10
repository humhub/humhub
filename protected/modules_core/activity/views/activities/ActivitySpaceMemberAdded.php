<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    

<strong><?php echo $user->displayName; ?></strong>

<?php if ($workspace != null && Wall::$currentType != Wall::TYPE_SPACE): ?>
    <?php echo Yii::t('ActivityModule.base', 'joined space'); ?>
    <strong>
        <?php echo Helpers::truncateText($workspace->name, 40); ?>
    </strong>

<?php else: ?>

    <?php echo Yii::t('ActivityModule.base', 'joined this space.'); ?>

<?php endif; ?>

<?php $this->endContent(); ?>

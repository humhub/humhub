<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>

<strong><?php echo $user->displayName; ?></strong>

<?php if ($workspace != null && Wall::$currentType != Wall::TYPE_SPACE): ?>
n    <?php echo Yii::t('ActivityModule.base', 'created a new space'); ?>

    <strong>
        <?php echo Helpers::truncateText($workspace->name, 25); ?> -
    </strong>

<?php else: ?>
    <?php echo Yii::t('ActivityModule.base', 'created this space.'); ?>
<?php endif; ?>

<?php $this->endContent(); ?>

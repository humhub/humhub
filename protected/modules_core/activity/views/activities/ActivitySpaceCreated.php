<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    

<strong><?php echo $user->displayName; ?></strong>

<?php if ($workspace != null && Wall::$currentType != Wall::TYPE_SPACE): ?>
    <?php echo Yii::t('ActivityModule.base', 'created a new workspace'); ?>

    <strong>
        <?php echo Helpers::truncateText($workspace->name, 25); ?> - 
    </strong>

<?php else: ?>
    <?php echo Yii::t('ActivityModule.base', 'created this workspace.'); ?>
<?php endif; ?>

<?php $this->endContent(); ?>

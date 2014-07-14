<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>

<?php if ($workspace != null && Wall::$currentType != Wall::TYPE_SPACE): ?>

    <?php echo Yii::t('ActivityModule.base', "%displayName% joined the space %spaceName%", array(
        '%displayName%' => '<strong>'.$user->displayName.'</strong>',
        '%spaceName%' => '<strong>'. Helpers::truncateText($workspace->name, 40).'</strong>'
    )); ?>

<?php else: ?>

    <?php echo Yii::t('ActivityModule.base', "%displayName% joined this space.", array(
        '<strong>'.$user->displayName.'</strong>'
    )); ?>

<?php endif; ?>

<?php $this->endContent(); ?>

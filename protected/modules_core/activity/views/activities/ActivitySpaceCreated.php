<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>

<?php if ($workspace != null && Wall::$currentType != Wall::TYPE_SPACE): ?>
    <?php echo Yii::t('ActivityModule.base', "%displayName% created the new space %spaceName%", array(
        '%displayName%' => '<strong>'.$user->displayName.'</strong>',
        '%spaceName%' => '<strong>'. Helpers::truncateText($workspace->name, 25).'</strong>'
    )); ?>

<?php else: ?>
    <?php echo Yii::t('ActivityModule.base', "%displayName% created this space.", array(
        '%displayName%' => '<strong>'.$user->displayName.'</strong>'
    ));
    ?>
<?php endif; ?>

<?php $this->endContent(); ?>


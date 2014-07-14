<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>

<?php echo Yii::t('CommentModule.activity', "<strong>%displayName%</strong> wrote a new comment ", array(
    '%displayName%' => $user->displayName
));
?>
"<?php
$text = ActivityModule::formatOutput($target->message);
echo Helpers::trimText($text, 100);
?>".

<?php $this->endContent(); ?>

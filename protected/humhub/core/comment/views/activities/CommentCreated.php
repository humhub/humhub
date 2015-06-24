<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>

<?php echo Yii::t('CommentModule.views_activities_CommentCreated', "%displayName% wrote a new comment ", array(
    '%displayName%' => '<strong>'. CHtml::encode($user->displayName) .'</strong>'
));
?>
"<?php
$text = ActivityModule::formatOutput($target->message);
echo Helpers::trimText($text, 100);
?>".

<?php $this->endContent(); ?>

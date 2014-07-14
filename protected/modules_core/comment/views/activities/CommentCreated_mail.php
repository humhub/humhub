<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity, 'showSpace' => true)); ?>

<?php echo Yii::t('CommentModule.activity', "%displayName% wrote a new comment ", array(
    '%displayName%' => '<strong>'. $user->displayName .'</strong>'
));
?>

<br/>
<?php $text = ActivityModule::formatOutput($target->message); ?>
<em>"<?php echo $text; ?>"</em>

<?php $this->endContent(); ?>

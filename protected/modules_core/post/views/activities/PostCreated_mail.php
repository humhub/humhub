<?php $this->beginContent('application.modules_core.activity.views.activityLayoutMail', array('activity' => $activity)); ?>                    

<?php
echo Yii::t('PostModule.base', '%displayName% created a new post.', array(
    '%displayName%' => '' // Displayed above in e-mail main layout, kept for same translation message as web activity.
));
?>

<br />

<em>"<?php echo ActivityModule::formatOutput($target->message); ?>"</em>

<?php $this->endContent(); ?>    
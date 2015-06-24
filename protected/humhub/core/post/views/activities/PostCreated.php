<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    
<?php
echo Yii::t('PostModule.views_activities_PostCreated', '%displayName% created a new post.', array(
    '%displayName%' => '<strong>' . CHtml::encode($user->displayName) . '</strong>'
));
?>
<?php $this->endContent(); ?>

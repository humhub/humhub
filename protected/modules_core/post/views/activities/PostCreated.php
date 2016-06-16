<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    
<?php
echo Yii::t('PostModule.views_activities_PostCreated', "<i class='fa fa-dot-circle-o color-circle-mentorship' style='margin-right: 5px;vertical-align: middle;color:blue !important'></i> %displayName% created a new post.", array(
    '%displayName%' => '<strong>' . CHtml::encode($user->displayName) . '</strong>'
));
?>
<?php $this->endContent(); ?>

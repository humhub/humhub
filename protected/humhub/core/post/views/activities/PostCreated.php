<?php $this->beginContent('@humhub/core/activity/views/activityLayout.php', array('activity' => $activity)); ?>                 
<?php
echo Yii::t('PostModule.views_activities_PostCreated', '%displayName% created a new post.', array(
    '%displayName%' => '<strong>' . \yii\helpers\Html::encode($user->displayName) . '</strong>'
));
?>
<?php $this->endContent(); ?>

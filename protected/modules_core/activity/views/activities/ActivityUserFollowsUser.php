<?php

/**
 * View File for the ActivityStreamWidget
 * 
 * @uses CActiveDataProvider $dataProvider The data provider for this model
 * @uses User $model The user model
 */
?>  
<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>
<?php

echo Yii::t('ActivityModule.base', '{user1} now follows {user2}.', array(
    '{user1}' => '<strong>' . $user->displayName . '</strong>',
    '{user2}' => '<strong>' . $target->displayName . '</strong>',
));
?>
<?php $this->endContent(); ?>

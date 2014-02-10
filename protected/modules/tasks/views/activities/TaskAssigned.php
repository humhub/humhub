<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    

<strong><?php echo $user->displayName; ?></strong>
assigned to task "<i><?php echo Helpers::truncateText($target->title, 25); ?></i>".

<?php $this->endContent(); ?>




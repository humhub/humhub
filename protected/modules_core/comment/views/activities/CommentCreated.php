<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    

<strong><?php echo $user->displayName; ?></strong>
wrote a new comment "<?php echo Helpers::trimText($target->message, 100); ?>".

<?php $this->endContent(); ?>

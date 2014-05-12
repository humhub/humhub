<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>

<strong><?php echo $user->displayName; ?></strong>
wrote a new comment "
<?php
    $text = ActivityModule::formatOutput($target->message);
    echo Helpers::trimText($text, 100);
?>
".

<?php $this->endContent(); ?>

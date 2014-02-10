
<?php $workspace = $this->getSpace(); ?>

<?php $this->widget('application.modules.tasks.widgets.TaskFormWidget', array('workspace' => $workspace)); ?>
<?php $this->widget('application.modules.tasks.widgets.TasksStreamWidget', array('type' => Wall::TYPE_SPACE, 'guid' => $workspace->guid)); ?>






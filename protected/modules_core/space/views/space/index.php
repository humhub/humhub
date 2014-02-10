
<?php
$this->widget('application.modules_core.post.widgets.PostFormWidget', array(
    'target' => Wall::TYPE_SPACE,
    'guid' => $this->getSpace()->guid
));
?>

<?php
$this->widget('application.modules_core.wall.widgets.WallStreamWidget', array(
    'type' => Wall::TYPE_SPACE,
    'guid' => $this->getSpace()->guid,
    'readonly' => ($this->getSpace()->status != Space::STATUS_ENABLED)));
?>


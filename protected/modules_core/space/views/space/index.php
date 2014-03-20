
<?php
$this->widget('application.modules_core.post.widgets.PostFormWidget', array(
    'contentContainer' => $this->getSpace(),
));
?>

<?php
$this->widget('application.modules_core.wall.widgets.WallStreamWidget', array(
    'contentContainer' => $this->getSpace()
)); 
?>


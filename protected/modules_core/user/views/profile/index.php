<?php

$this->widget('application.modules_core.post.widgets.PostFormWidget', array(
    'target' => Wall::TYPE_USER, 'guid' => $this->getUser()->guid
));
?>

<?php

$this->widget('application.modules_core.wall.widgets.WallStreamWidget', array(
    'type' => Wall::TYPE_USER,
    'guid' => $this->getUser()->guid
));
?>
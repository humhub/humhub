<?php

/**
 * User posts page of directory
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 */
?>
<?php

$this->widget('application.modules_core.post.widgets.PostFormWidget', array(
    'target' => Wall::TYPE_USER,
    'guid' => Yii::app()->user->guid
));
?>
<?php

$this->widget('application.modules_core.wall.widgets.WallStreamWidget', array(
    'type' => Wall::TYPE_COMMUNITY));
?>


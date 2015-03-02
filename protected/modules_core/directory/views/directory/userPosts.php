<?php

if (!Yii::app()->user->isGuest) {
    $this->widget('application.modules_core.post.widgets.PostFormWidget', array(
        'contentContainer' => Yii::app()->user->model
    ));
}

$this->widget('application.modules_core.wall.widgets.WallStreamWidget', array(
    'type' => Wall::TYPE_COMMUNITY));
?>

